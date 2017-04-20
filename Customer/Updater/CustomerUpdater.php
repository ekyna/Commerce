<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Updater;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class CustomerUpdater
 * @package Ekyna\Component\Commerce\Customer\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerUpdater implements CustomerUpdaterInterface
{
    protected PersistenceHelperInterface $persistenceHelper;

    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    public function handlePaymentInsert(PaymentInterface $payment): bool
    {
        if ($this->supports($payment) && $this->isAcceptedPayment($payment)) {
            return $this->updateCustomerBalance($payment);
        }

        return false;
    }

    public function handlePaymentUpdate(PaymentInterface $payment): bool
    {
        if (!$this->supports($payment)) {
            return false;
        }

        $stateCs = $this->persistenceHelper->getChangeSet($payment, 'state');
        $amountCs = $this->persistenceHelper->getChangeSet($payment, 'realAmount');

        $acceptedStates = $this->getAcceptedStates($payment);

        // By state change
        if (!empty($stateCs) && ($stateCs[0] !== $stateCs[1])) {
            $fromAccepted = in_array($stateCs[0], $acceptedStates, true);
            $toAccepted = in_array($stateCs[1], $acceptedStates, true);

            // If payment state has changed from or to a accepted state
            if ($fromAccepted xor $toAccepted) {
                // Update the customer balance, use old amount if state changed from accepted.
                $amount = ($fromAccepted && !empty($amountCs)) ? ($amountCs[0] ?? new Decimal(0)) : null;
                return $this->updateCustomerBalance($payment, $amount);
            }
        }

        // By Amount change
        if (!empty($amountCs)) {
            $amountDelta = ($amountCs[1] ?? new Decimal(0))->sub($amountCs[0] ?? new Decimal(0)); // New - Old
            // If amount changed and payment is accepted
            if (0 != $amountDelta && in_array($payment->getState(), $acceptedStates, true)) {
                return $this->updateCustomerBalance($payment, $amountDelta);
            }
        }

        return false;
    }

    public function handlePaymentDelete(PaymentInterface $payment): bool
    {
        if ($this->supports($payment) && $this->isAcceptedPayment($payment)) {
            $payment->setState(PaymentStates::STATE_CANCELED);

            return $this->updateCustomerBalance($payment);
        }

        return false;
    }

    public function updateCreditBalance(CustomerInterface $customer, Decimal $amount, bool $relative = false): bool
    {
        // Switch to parent if available
        if ($customer->hasParent()) {
            $customer = $customer->getParent();
        }

        $old = $customer->getCreditBalance();
        $new = $relative ? $old + $amount : $amount;

        if (!$old->equals($new)) {
            $customer->setCreditBalance($new);
            $this->persistenceHelper->persistAndRecompute($customer, false);

            return true;
        }

        return false;
    }

    public function updateOutstandingBalance(CustomerInterface $customer, Decimal $amount, bool $relative = false): bool
    {
        // Switch to parent if available
        if ($customer->hasParent()) {
            $customer = $customer->getParent();
        }

        $old = $customer->getOutstandingBalance();
        $new = $relative ? $old + $amount : $amount;

        if (!$old->equals($new)) {
            $customer->setOutstandingBalance($new);
            $this->persistenceHelper->persistAndRecompute($customer, false);

            return true;
        }

        return false;
    }

    /**
     * Updates the customer's balance regarding the payment method.
     *
     * @param PaymentInterface $payment
     * @param Decimal|null     $amount The previous amount (update case)
     *
     * @return bool Whether the customer has been changed.
     */
    protected function updateCustomerBalance(PaymentInterface $payment, Decimal $amount = null): bool
    {
        if (null === $customer = $payment->getSale()->getCustomer()) {
            // TODO Deals with customer change
            return false;
        }

        $amount = $amount ?: $payment->getRealAmount();
        if ($payment->isRefund()) {
            $amount = $amount->negate();
        }
        if ($this->isAcceptedPayment($payment)) {
            $amount = $amount->negate();
        }

        if ($payment->getMethod()->isCredit()) {
            return $this->updateCreditBalance($customer, $amount, true);
        } elseif ($payment->getMethod()->isOutstanding()) {
            return $this->updateOutstandingBalance($customer, $amount, true);
        }

        return false;
    }

    /**
     * Returns whether the credit/outstanding payment is accepted.
     */
    protected function isAcceptedPayment(PaymentInterface $payment): bool
    {
        return in_array($payment->getState(), $this->getAcceptedStates($payment), true);
    }

    /**
     * Returns the payment accepted states.
     */
    protected function getAcceptedStates(PaymentInterface $payment): array
    {
        $acceptedStates = PaymentStates::getPaidStates();

        if ($payment->getMethod()->isOutstanding()) {
            $acceptedStates[] = PaymentStates::STATE_EXPIRED;
        }

        return $acceptedStates;
    }

    /**
     * Returns whether the payment is supported.
     */
    protected function supports(PaymentInterface $payment): bool
    {
        if (null === $method = $payment->getMethod()) {
            throw new RuntimeException('Payment method must be set.');
        }

        if ($method->isCredit() || $method->isOutstanding()) {
            return true;
        }

        return false;
    }
}

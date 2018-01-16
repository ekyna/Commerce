<?php

namespace Ekyna\Component\Commerce\Payment\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;

/**
 * Class PaymentSubjectStateResolver
 * @package Ekyna\Component\Commerce\Payment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentSubjectStateResolver implements StateResolverInterface
{
    /**
     * @var PaymentCalculatorInterface
     */
    protected $paymentCalculator;


    /**
     * Constructor.
     *
     * @param PaymentCalculatorInterface $paymentCalculator
     */
    public function __construct(PaymentCalculatorInterface $paymentCalculator)
    {
        $this->paymentCalculator = $paymentCalculator;
    }

    /**
     * @inheritDoc
     */
    public function resolve($subject)
    {
        if (!$subject instanceof PaymentSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . PaymentSubjectInterface::class);
        }

        if (0 === $subject->getPayments()->count()) {
            return $this->setState($subject, PaymentStates::STATE_NEW);
        }

        // This method uses the calculated sale's payment totals.
        // Makes sure to update them before any call of this method.

        $grandTotal = $subject->getGrandTotal();
        $currency = $subject->getCurrency()->getCode();

        // COMPLETED paid total equals grand total and no accepted/expired outstanding
        if (
            0 === Money::compare($subject->getPaidTotal(), $grandTotal, $currency) &&
            0 == $subject->getOutstandingAccepted() && 0 == $subject->getOutstandingExpired()
        ) {
            return $this->setState($subject, PaymentStates::STATE_COMPLETED);
        }

        $fullFill = function ($amount) use ($grandTotal, $currency) {
            return 0 <= Money::compare($amount, $grandTotal, $currency);
        };

        // CAPTURED paid total plus accepted outstanding total is greater than grand total
        if ($fullFill($subject->getPaidTotal() + $subject->getOutstandingAccepted())) {
            return $this->setState($subject, PaymentStates::STATE_CAPTURED);
        }

        // OUTSTANDING expired total is greater than zero
        if (0 < $subject->getOutstandingExpired()) {
            return $this->setState($subject, PaymentStates::STATE_OUTSTANDING);
        }

        // PENDING total is greater than zero
        if ($fullFill($subject->getPaidTotal() + $subject->getOutstandingAccepted() + $subject->getPendingTotal())) {
            return $this->setState($subject, PaymentStates::STATE_PENDING);
        }

        // REFUNDED total is greater than or equals the grand total
        if ($fullFill($this->paymentCalculator->calculateRefundedTotal($subject))) {
            return $this->setState($subject, PaymentStates::STATE_REFUNDED);
        }

        // FAILED total is greater than or equals the grand total
        if ($fullFill($this->paymentCalculator->calculateFailedTotal($subject))) {
            return $this->setState($subject, PaymentStates::STATE_FAILED);
        }

        // FAILED total is greater than or equals the grand total
        if ($fullFill($this->paymentCalculator->calculateCanceledTotal($subject))) {
            return $this->setState($subject, PaymentStates::STATE_CANCELED);
        }

        // NEW (default) state
        return $this->setState($subject, PaymentStates::STATE_NEW);
    }

    /**
     * Sets the payment state.
     *
     * @param PaymentSubjectInterface $subject
     * @param string                  $state
     *
     * @return bool Whether the shipment state has been updated.
     */
    protected function setState(PaymentSubjectInterface $subject, $state)
    {
        if ($state !== $subject->getPaymentState()) {
            $subject->setPaymentState($state);

            return true;
        }

        return false;
    }
}

<?php

namespace Ekyna\Component\Commerce\Payment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Updater\CustomerUpdaterInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractPaymentListener
 * @package Ekyna\Component\Commerce\Payment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPaymentListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var KeyGeneratorInterface
     */
    protected $keyGenerator;

    /**
     * @var CustomerUpdaterInterface
     */
    protected $customerUpdater;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the number generator.
     *
     * @param NumberGeneratorInterface $numberGenerator
     */
    public function setNumberGenerator(NumberGeneratorInterface $numberGenerator)
    {
        $this->numberGenerator = $numberGenerator;
    }

    /**
     * Sets the key generator.
     *
     * @param KeyGeneratorInterface $keyGenerator
     */
    public function setKeyGenerator(KeyGeneratorInterface $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * Sets the customer updater.
     *
     * @param CustomerUpdaterInterface $customerUpdater
     */
    public function setCustomerUpdater(CustomerUpdaterInterface $customerUpdater)
    {
        $this->customerUpdater = $customerUpdater;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        // Generate number and key
        $changed = $this->generateNumber($payment);
        $changed |= $this->generateKey($payment);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        $sale = $payment->getSale(); // TODO wtf ?
        $sale->addPayment($payment);

        // Impact customer balance only for paid payments
        if (PaymentStates::isPaidState($payment->getState())) {
            $this->updateCustomerBalance($payment);
        }

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        // Generate number and key
        $changed = $this->generateNumber($payment);
        $changed |= $this->generateKey($payment);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        if ($this->persistenceHelper->isChanged($payment, ['amount', 'state'])) {
            $this->scheduleSaleContentChangeEvent($payment->getSale());

            // Abort if not credit/outstanding payment
            if (!($payment->getMethod()->isCredit() || $payment->getMethod()->isOutstanding())) {
                return;
            }

            $stateCs = $this->persistenceHelper->getChangeSet($payment, 'state');
            $amountCs = $this->persistenceHelper->getChangeSet($payment, 'amount');

            // If payment state has changed from or to a paid state
            if (PaymentStates::hasChangedFromPaid($stateCs) || PaymentStates::hasChangedToPaid($stateCs)) {
                // Update the customer balance
                $this->updateCustomerBalance($payment, isset($amountCs[0]) ? $amountCs[0] : null);

                return;
            }

            if (!empty($amountCs)) {
                $amountDelta = $amountCs[0] - $amountCs[1]; // Old - New
                // If amount changed and payment is paid
                if (0 != $amountDelta && PaymentStates::isPaidState($payment->getState())) {
                    $this->updateCustomerBalance($payment, -$amountDelta);
                }
            }
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        // Update the customer balance
        $payment->setState(PaymentStates::STATE_CANCELLED);
        $this->updateCustomerBalance($payment);

        $this->scheduleSaleContentChangeEvent($payment->getSale());
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        // TODO non offline methods can't be changed

        //$payment = $this->getPaymentFromEvent($event);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        if (!in_array($payment->getState(), PaymentStates::getDeletableStates())) {
            throw new IllegalOperationException(); // TODO reason message
        }
    }

    /**
     * Generates the payment number.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the payment has been changed or not.
     */
    protected function generateNumber(PaymentInterface $payment)
    {
        if (0 == strlen($payment->getNumber())) {
            $this->numberGenerator->generate($payment);

            return true;
        }

        return false;
    }

    /**
     * Updates the customer's balance regarding to the payment method.
     *
     * @param PaymentInterface $payment
     * @param float            $amount The previous amount (update case)
     */
    protected function updateCustomerBalance(PaymentInterface $payment, $amount = null)
    {
        if (null === $customer = $payment->getSale()->getCustomer()) {
            return;
        }

        $amount = $amount ?: $payment->getAmount();
        if (PaymentStates::isPaidState($payment->getState())) {
            $amount = -$amount;
        }

        if ($payment->getMethod()->isCredit()) {
            $this->customerUpdater->updateCreditBalance($customer, $amount, true);
        } elseif ($payment->getMethod()->isOutstanding()) {
            $this->customerUpdater->updateOutstandingBalance($customer, $amount, true);
        }
    }

    /**
     * Generates the payment key.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the payment has been changed or not.
     */
    protected function generateKey(PaymentInterface $payment)
    {
        if (0 == strlen($payment->getKey())) {
            $this->keyGenerator->generate($payment);

            return true;
        }

        return false;
    }

    /**
     * Schedules the sale content change event.
     *
     * @param SaleInterface $sale
     */
    abstract protected function scheduleSaleContentChangeEvent(SaleInterface $sale);

    /**
     * Returns the payment from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return PaymentInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getPaymentFromEvent(ResourceEventInterface $event);
}

<?php

namespace Ekyna\Component\Commerce\Payment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Updater\CustomerUpdaterInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;
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
     * @var GeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var GeneratorInterface
     */
    protected $keyGenerator;

    /**
     * @var PaymentUpdaterInterface
     */
    protected $paymentUpdater;

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
     * @param GeneratorInterface $generator
     */
    public function setNumberGenerator(GeneratorInterface $generator)
    {
        $this->numberGenerator = $generator;
    }

    /**
     * Sets the key generator.
     *
     * @param GeneratorInterface $generator
     */
    public function setKeyGenerator(GeneratorInterface $generator)
    {
        $this->keyGenerator = $generator;
    }

    /**
     * Sets the payment updater.
     *
     * @param PaymentUpdaterInterface $updater
     */
    public function setPaymentUpdater(PaymentUpdaterInterface $updater)
    {
        $this->paymentUpdater = $updater;
    }

    /**
     * Sets the customer updater.
     *
     * @param CustomerUpdaterInterface $updater
     */
    public function setCustomerUpdater(CustomerUpdaterInterface $updater)
    {
        $this->customerUpdater = $updater;
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

        // Fix real amount
        $changed |= $this->paymentUpdater->fixRealAmount($payment);

        // Completed state
        $changed |= $this->handleCompletedState($payment);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        $sale = $this->getSaleFromPayment($payment);

        $this->customerUpdater->handlePaymentInsert($payment);

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws LogicException
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        // Method and currency can't be changed
        if ($this->persistenceHelper->isChanged($payment, 'method')) {
            throw new LogicException("Payment method can't be changed.");
        }
        if ($this->persistenceHelper->isChanged($payment, 'currency')) {
            throw new LogicException("Payment currency can't be changed.");
        }

        // Generate number and key
        $changed = $this->generateNumber($payment);
        $changed |= $this->generateKey($payment);

        if ($this->persistenceHelper->isChanged($payment, 'amount')) {
            $changed |= $this->paymentUpdater->fixRealAmount($payment);
        } elseif ($this->persistenceHelper->isChanged($payment, 'realAmount')) {
            $changed |= $this->paymentUpdater->fixAmount($payment);
        }

        if ($this->persistenceHelper->isChanged($payment, 'state')) {
            $changed |= $this->handleCompletedState($payment);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        if ($this->persistenceHelper->isChanged($payment, ['realAmount', 'state'])) {
            $this->scheduleSaleContentChangeEvent($this->getSaleFromPayment($payment));

            $this->customerUpdater->handlePaymentUpdate($payment);
        }
    }

    /**
     * Handle the 'completed' state.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether or not the shipment has been changed.
     */
    protected function handleCompletedState(PaymentInterface $payment)
    {
        $changed = false;

        $isCompleted = PaymentStates::isCompletedState($payment, true);
        $isSet = !is_null($payment->getCompletedAt());

        if ($isCompleted && !$isSet) {
            $payment->setCompletedAt(new \DateTime());
            $changed = true;
        } elseif (!$isCompleted && $isSet) {
            $payment->setCompletedAt(null);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        $this->customerUpdater->handlePaymentDelete($payment);

        $sale = $this->getSaleFromPayment($payment);

        $sale->removePayment($payment);

        $this->scheduleSaleContentChangeEvent($sale);
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

        // Pre load sale's payments
        $payment->getSale()->getPayments()->toArray();

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
        if (!empty($payment->getNumber())) {
            return false;
        }

        $payment->setNumber($this->numberGenerator->generate($payment));

        return true;
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
        if (!empty($payment->getKey())) {
            return false;
        }

        $payment->setKey($this->keyGenerator->generate($payment));

        return true;
    }

    /**
     * Returns the shipment's sale.
     *
     * @param PaymentInterface $payment
     *
     * @return SaleInterface|PaymentSubjectInterface
     */
    protected function getSaleFromPayment(PaymentInterface $payment)
    {
        if (null === $sale = $payment->getSale()) {
            $cs = $this->persistenceHelper->getChangeSet($payment, $this->getSalePropertyPath());
            if (!empty($cs)) {
                $sale = $cs[0];
            }
        }

        if (!$sale instanceof SaleInterface) {
            throw new RuntimeException("Failed to retrieve shipment's sale.");
        }

        // Pre-load / initialize the sale
        $sale->getState();

        return $sale;
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

    /**
     * Returns the invoice's sale property path.
     *
     * @return string
     */
    abstract protected function getSalePropertyPath();
}

<?php

namespace Ekyna\Component\Commerce\Payment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Updater\CustomerUpdaterInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
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

        // Generate number and key
        $changed = $this->generateNumber($payment);
        $changed |= $this->generateKey($payment);

        if ($this->persistenceHelper->isChanged($payment, 'state')) {
            $changed |= $this->handleCompletedState($payment);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        if ($this->persistenceHelper->isChanged($payment, 'method')) {
            $methodCs = $this->persistenceHelper->getChangeSet($payment, 'method');
            /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface $fromMethod */
            $fromMethod = $methodCs[0];
            /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface $toMethod */
            $toMethod = $methodCs[1];

            if ($fromMethod->isManual() && !$toMethod->isManual()) {
                throw new LogicException("Payment method can't be changed from manual to non manual method.");
            } elseif (!$fromMethod->isManual()) {
                throw new LogicException("Payment method can't be changed.");
            }
        }

        if ($this->persistenceHelper->isChanged($payment, ['amount', 'state'])) {
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

        $state = $payment->getState();
        $completedAt = $payment->getCompletedAt();

        if (PaymentStates::isPaidState($state) && null === $completedAt) {
            $payment->setCompletedAt(new \DateTime());
            $changed = true;
        } elseif (!PaymentStates::isPaidState($state) && null !== $completedAt) {
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
        if (0 == strlen($payment->getNumber())) {
            $this->numberGenerator->generate($payment);

            return true;
        }

        return false;
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

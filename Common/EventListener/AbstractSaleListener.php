<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractSaleListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleListener
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
     * @var SaleUpdaterInterface
     */
    protected $saleUpdater;

    /**
     * @var StateResolverInterface
     */
    protected $stateResolver;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
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
     * Sets the sale updater.
     *
     * @param SaleUpdaterInterface $saleUpdater
     */
    public function setSaleUpdater(SaleUpdaterInterface $saleUpdater)
    {
        $this->saleUpdater = $saleUpdater;
    }

    /**
     * Sets the state resolver.
     *
     * @param StateResolverInterface $stateResolver
     */
    public function setStateResolver(StateResolverInterface $stateResolver)
    {
        $this->stateResolver = $stateResolver;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        $changed = false;

        // Generate number and key
        $changed |= $this->generateNumber($sale);
        $changed |= $this->generateKey($sale);

        // Handle customer information
        $changed |= $this->handleInformation($sale);

        // Handle payment term
        $changed |= $this->handlePaymentTerm($sale);

        // Update discounts
        $changed |= $this->saleUpdater->updateDiscounts($sale, true);

        // Update taxation
        $changed |= $this->saleUpdater->updateTaxation($sale, true);

        // Update totals
        $changed |= $this->saleUpdater->updateTotals($sale);

        // Update outstanding
        $changed |= $this->saleUpdater->updateOutstanding($sale);

        // Update state
        $changed |= $this->updateState($sale);

        /**
         * TODO Resource behaviors.
         */
        if (null === $sale->getUpdatedAt()) {
            $sale->setUpdatedAt(new \DateTime());
            $changed = true;
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($sale);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        $changed = false;

        // Generate number and key
        $changed |= $this->generateNumber($sale);
        $changed |= $this->generateKey($sale);

        // Handle customer information
        $changed |= $this->handleInformation($sale);

        // If customer has changed
        if ($this->persistenceHelper->isChanged($sale, 'customer')) {
            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $oldCustomer */
            list($oldCustomer) = $this->persistenceHelper->getChangeSet($sale, 'customer');

            // Restore customer outstanding amount
            if (null !== $oldCustomer) {
                $changed |= $this->saleUpdater->clearOutstanding($sale, $oldCustomer);
            }
        }

        // Handle payment term
        $changed |= $this->handlePaymentTerm($sale);

        /**
         * TODO Resource behaviors.
         */
        $sale->setUpdatedAt(new \DateTime());

        // Recompute to get an update-to-date change set.
        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($sale);
        }

        // Schedule content change
        if ($this->persistenceHelper->isChanged($sale, 'paymentTerm')) {
            $this->scheduleContentChangeEvent($sale);
        }

        // Handle addresses changes
        /* TODO ? if ($this->persistenceHelper->isChanged($sale, ['deliveryAddress', 'sameAddress'])) {
            $this->scheduleAddressChangeEvent($sale);
        }*/
    }

    /**
     * Address change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onAddressChange(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        $changed = false;

        // Update discounts
        if ($this->isDiscountUpdateNeeded($sale)) {
            $changed |= $this->saleUpdater->updateDiscounts($sale, true);
        }

        // Update taxation
        if ($this->isTaxationUpdateNeeded($sale)) {
            $changed |= $this->saleUpdater->updateTaxation($sale, true);
        } elseif ($this->isShipmentTaxationUpdateNeeded($sale)) {
            $changed |= $this->saleUpdater->updateShipmentTaxation($sale, true);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($sale);

            $this->scheduleContentChangeEvent($sale);
        }
    }

    /**
     * Content (item/adjustment/payment/shipment) change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        // Update totals
        $changed = $this->saleUpdater->updateTotals($sale);

        // Update outstanding
        $changed |= $this->saleUpdater->updateOutstanding($sale);

        // Update state
        $changed |= $this->updateState($sale);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($sale);
        }
    }

    /**
     * State change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onStateChange(ResourceEventInterface $event)
    {

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
        if ($event->getHard()) {
            return;
        }

        $sale = $this->getSaleFromEvent($event);

        // Stop if sale has valid payments
        if (null !== $payments = $sale->getPayments()) {
            foreach ($payments as $payment) {
                if (!PaymentStates::isDeletableState($payment->getState())) {
                    throw new IllegalOperationException(); // TODO reason message
                }
            }
        }
    }

    /**
     * Returns whether or not the discount adjustments should be updated.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function isDiscountUpdateNeeded(SaleInterface $sale)
    {
        $saleCs = $this->persistenceHelper->getChangeSet($sale);

        // Watch for customer group or customer change
        if (isset($saleCs['customerGroup']) || isset($saleCs['customer'])) {
            return true;
        }

        // Watch for invoice country change
        $oldCountry = $newCountry = null;

        $oldAddress = isset($saleCs['invoiceAddress']) ? $saleCs['invoiceAddress'][0] : $sale->getInvoiceAddress();
        if (null !== $oldAddress) {
            $oldAddressCs = $this->persistenceHelper->getChangeSet($oldAddress);
            $oldCountry = isset($oldAddressCs['country']) ? $oldAddressCs['country'][0] : $oldAddress->getCountry();
        }

        // Resolve the new tax resolution target country
        if (null !== $newAddress = $sale->getInvoiceAddress()) {
            $newCountry = $newAddress->getCountry();
        }

        if ($oldCountry !== $newCountry) {
            return true;
        }

        return false;
    }

    /**
     * Returns whether or not the taxation adjustments should be updated.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function isTaxationUpdateNeeded(SaleInterface $sale)
    {
        // TODO Abort if completed (order) ?

        // TODO Get tax resolution mode. (by invoice/delivery/origin).

        $saleCs = $this->persistenceHelper->getChangeSet($sale);

        // Watch for tax exempt, customer group or customer change
        if (isset($saleCs['taxExempt']) || isset($saleCs['customerGroup']) || isset($saleCs['customer'])) {
            return true;
        }

        // Watch for delivery country change
        $oldCountry = $newCountry = null;

        // Resolve the old tax resolution target country
        $oldSameAddress = isset($saleCs['sameAddress']) ? $saleCs['sameAddress'][0] : $sale->isSameAddress();
        if ($oldSameAddress) {
            $oldAddress = isset($saleCs['invoiceAddress']) ? $saleCs['invoiceAddress'][0] : $sale->getInvoiceAddress();
        } else {
            $oldAddress = isset($saleCs['deliveryAddress']) ? $saleCs['deliveryAddress'][0] : $sale->getDeliveryAddress();
        }
        if (null !== $oldAddress) {
            $oldAddressCs = $this->persistenceHelper->getChangeSet($oldAddress);
            $oldCountry = isset($oldAddressCs['country']) ? $oldAddressCs['country'][0] : $oldAddress->getCountry();
        }

        // Resolve the new tax resolution target country
        $newAddress = $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress();
        if (null !== $newAddress) {
            $newCountry = $newAddress->getCountry();
        }

        if ($oldCountry !== $newCountry) {
            return true;
        }

        return false;
    }

    /**
     * Returns whether or not the shipment related taxation adjustments should be updated.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function isShipmentTaxationUpdateNeeded(SaleInterface $sale)
    {
        return $this->persistenceHelper->isChanged($sale, 'preferredShipmentMethod');
    }

    /**
     * Generates the number.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale number has been generated or not.
     */
    protected function generateNumber(SaleInterface $sale)
    {
        if (0 == strlen($sale->getNumber())) {
            $this->numberGenerator->generate($sale);

            return true;
        }

        return false;
    }

    /**
     * Generates the key.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale key has been generated or not.
     */
    protected function generateKey(SaleInterface $sale)
    {
        if (0 == strlen($sale->getKey())) {
            $this->keyGenerator->generate($sale);

            return true;
        }

        return false;
    }

    /**
     * Handles the customer information.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function handleInformation(SaleInterface $sale)
    {
        $changed = false;

        if (null !== $customer = $sale->getCustomer()) {
            // Customer group
            if (null === $sale->getCustomerGroup() && null !== $customer->getCustomerGroup()) {
                $sale->setCustomerGroup($customer->getCustomerGroup());
                $changed = true;
            }

            // Email
            if (0 == strlen($sale->getEmail())) {
                $sale->setEmail($customer->getEmail());
                $changed = true;
            }

            // Identity
            if (0 == strlen($sale->getCompany())) {
                $sale->setCompany($customer->getCompany());
                $changed = true;
            }
            if (0 == strlen($sale->getGender())) {
                $sale->setGender($customer->getGender());
                $changed = true;
            }
            if (0 == strlen($sale->getFirstName())) {
                $sale->setFirstName($customer->getFirstName());
                $changed = true;
            }
            if (0 == strlen($sale->getLastName())) {
                $sale->setLastName($customer->getLastName());
                $changed = true;
            }

            // Invoice address
            if (null === $sale->getInvoiceAddress() && null !== $address = $customer->getDefaultInvoiceAddress()) {
                $changed |= $this->saleUpdater->updateInvoiceAddressFromAddress($sale, $address);
            }

            // Delivery address
            if ($sale->isSameAddress()) {
                // Remove unused address
                if (null !== $address = $sale->getDeliveryAddress()) {
                    $sale->setDeliveryAddress(null);
                    $this->persistenceHelper->remove($address, true);
                }
            } else if (null === $sale->getDeliveryAddress() && null !== $address = $customer->getDefaultDeliveryAddress()) {
                $changed |= $this->saleUpdater->updateDeliveryAddressFromAddress($sale, $address);
            }
        }

        return $changed;
    }

    /**
     * Handles the customer / payment term.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function handlePaymentTerm(SaleInterface $sale)
    {
        $changed = false;

        // New customer's payment term
        if (null !== $customer = $sale->getCustomer()) {
            if ($sale->getPaymentTerm() !== $term = $customer->getPaymentTerm()) {
                $sale->setPaymentTerm($term);
                $changed = true;
            }
        } elseif ($sale->getPaymentTerm()) {
            $sale->setPaymentTerm(null);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the state.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateState(SaleInterface $sale)
    {
        if ($this->stateResolver->resolve($sale)) {
            $this->scheduleStateChangeEvent($sale);

            return true;
        }

        return false;
    }

    /**
     * Returns the sale from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SaleInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getSaleFromEvent(ResourceEventInterface $event);

    /**
     * Schedule the content change event handler.
     *
     * @param SaleInterface $sale
     */
    abstract protected function scheduleContentChangeEvent(SaleInterface $sale);

    /**
     * Schedule the state change event handler.
     *
     * @param SaleInterface $sale
     */
    abstract protected function scheduleStateChangeEvent(SaleInterface $sale);
}

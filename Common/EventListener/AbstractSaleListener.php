<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Pricing\Updater\PricingUpdaterInterface;
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
     * @var PricingUpdaterInterface
     */
    protected $pricingUpdater;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

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
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the number generator.
     *
     * @param NumberGeneratorInterface $generator
     */
    public function setNumberGenerator(NumberGeneratorInterface $generator)
    {
        $this->numberGenerator = $generator;
    }

    /**
     * Sets the key generator.
     *
     * @param KeyGeneratorInterface $generator
     */
    public function setKeyGenerator(KeyGeneratorInterface $generator)
    {
        $this->keyGenerator = $generator;
    }

    /**
     * Sets the pricingUpdater.
     *
     * @param PricingUpdaterInterface $updater
     */
    public function setPricingUpdater(PricingUpdaterInterface $updater)
    {
        $this->pricingUpdater = $updater;
    }

    /**
     * Sets the sale factory.
     *
     * @param SaleFactoryInterface $saleFactory
     */
    public function setSaleFactory(SaleFactoryInterface $saleFactory)
    {
        $this->saleFactory = $saleFactory;
    }

    /**
     * Sets the sale updater.
     *
     * @param SaleUpdaterInterface $updater
     */
    public function setSaleUpdater(SaleUpdaterInterface $updater)
    {
        $this->saleUpdater = $updater;
    }

    /**
     * Sets the state resolver.
     *
     * @param StateResolverInterface $resolver
     */
    public function setStateResolver(StateResolverInterface $resolver)
    {
        $this->stateResolver = $resolver;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        if ($this->handleInsert($sale)) {
            $this->persistenceHelper->persistAndRecompute($sale, false);
        }
    }

    /**
     * Handles the sale insertion.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed.
     *
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    protected function handleInsert(SaleInterface $sale)
    {
        $changed = false;

        // Generate number and key
        $changed |= $this->updateNumber($sale);
        $changed |= $this->updateKey($sale);

        // Handle customer information
        $changed |= $this->updateInformation($sale, true);

        // Update pricing
        $changed |= $this->pricingUpdater->updateVatNumberSubject($sale);

        // Update outstanding
        $changed |= $this->saleUpdater->updatePaymentTerm($sale);

        // Update discounts
        $changed |= $this->saleUpdater->updateDiscounts($sale, true);

        // Update taxation
        $changed |= $this->saleUpdater->updateTaxation($sale, true);

        // Update totals
        $changed |= $this->saleUpdater->updateTotals($sale);

        // Update state
        $changed |= $this->updateState($sale);

        return $changed;
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        $this->preventForbiddenChange($sale);

        if ($this->handleUpdate($sale)) {
            $this->persistenceHelper->persistAndRecompute($sale, false);
        }

        // Schedule content change
        if ($this->persistenceHelper->isChanged($sale, ['paymentTerm', 'shipmentAmount'])) {
            $this->scheduleContentChangeEvent($sale);
        }

        // Handle addresses changes
        /* TODO ? if ($this->persistenceHelper->isChanged($sale, ['deliveryAddress', 'sameAddress'])) {
            $this->scheduleAddressChangeEvent($sale);
        }*/
    }

    /**
     * Handles the sale update.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed.
     */
    protected function handleUpdate(SaleInterface $sale)
    {
        $changed = false;

        // Generate number and key
        $changed |= $this->updateNumber($sale);
        $changed |= $this->updateKey($sale);

        // Handle customer information
        $changed |= $this->updateInformation($sale, true);

        // Update pricing
        if ($this->persistenceHelper->isChanged($sale, 'vatNumber')) {
            $changed |= $this->pricingUpdater->updateVatNumberSubject($sale);
        }

        // If customer has changed
        if ($this->persistenceHelper->isChanged($sale, 'customer')) {
            $changed |= $this->saleUpdater->updatePaymentTerm($sale);

            // TODO Update customer's balances

            // TODO For each payments
            // If payment is paid or has changed from paid state

            // TODO For each (credit)invoices
        }

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

        return $changed;
    }

    /**
     * Address change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onAddressChange(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        if ($this->persistenceHelper->isScheduledForRemove($sale)) {
            $event->stopPropagation();

            return;
        }

        if ($this->handleAddressChange($sale)) {
            $this->persistenceHelper->persistAndRecompute($sale, false);

            $this->scheduleContentChangeEvent($sale);
        }
    }

    /**
     * Handles the address change.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function handleAddressChange(SaleInterface $sale)
    {
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

        return $changed;
    }

    /**
     * Content (item/adjustment/payment/shipment/invoice) change event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        if ($this->persistenceHelper->isScheduledForRemove($sale)) {
            $event->stopPropagation();

            return;
        }

        $this->handleContentChange($sale);

        // Reflect content change on update timestamp
        $sale->setUpdatedAt(new\DateTime());

        $this->persistenceHelper->persistAndRecompute($sale, false);
    }

    /**
     * Handles the content change.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     *
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    protected function handleContentChange(SaleInterface $sale)
    {
        // Update outstanding date
        $changed = $this->saleUpdater->updateOutstandingDate($sale);

        // Update totals
        $changed |= $this->saleUpdater->updateTotals($sale);

        // Update state
        $changed |= $this->updateState($sale);

        return $changed;
    }

    /**
     * State change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onStateChange(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        if ($this->persistenceHelper->isScheduledForRemove($sale)) {
            $event->stopPropagation();

            return;
        }

        $this->handleStateChange($sale);
    }

    /**
     * Handles the state change.
     *
     * @param SaleInterface $sale
     */
    protected function handleStateChange(SaleInterface $sale)
    {
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        if (null !== $customer = $sale->getCustomer()) {
            $invoiceDefault = $customer->getDefaultInvoiceAddress(true);
            $deliveryDefault = $customer->getDefaultDeliveryAddress(true);

            if (null === $sale->getInvoiceAddress() && null !== $invoiceDefault) {
                $sale->setInvoiceAddress(
                    $this->saleFactory->createAddressForSale($sale, $invoiceDefault)
                );
            }

            if (null === $sale->getDeliveryAddress() && null !== $deliveryDefault && $deliveryDefault !== $invoiceDefault) {
                $sale
                    ->setSameAddress(false)
                    ->setDeliveryAddress(
                        $this->saleFactory->createAddressForSale($sale, $invoiceDefault)
                    );
            }
        }
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        $this->updateInformation($sale);

        $this->pricingUpdater->updateVatNumberSubject($sale);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws Exception\IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        if ($event->getHard()) {
            return;
        }

        if (null === $sale = $this->getSaleFromEvent($event)) {
            return;
        }

        // Stop if sale has valid payments
        foreach ($sale->getPayments() as $payment) {
            if (!PaymentStates::isDeletableState($payment->getState())) {
                throw new Exception\IllegalOperationException(
                    'Sale has valid payments and therefore cannot be deleted.'
                ); // TODO Translation
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

        // Watch for auto discount, customer group or customer change
        if (isset($saleCs['autoDiscount']) || isset($saleCs['customerGroup']) || isset($saleCs['customer'])) {
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
        // TODO (Order) Abort if "completed" and not "has changed for completed"

        // TODO Get tax resolution mode. (by invoice/delivery/origin).

        $saleCs = $this->persistenceHelper->getChangeSet($sale);

        // Watch for tax exempt, customer or vatValid change
        if (isset($saleCs['taxExempt']) || isset($saleCs['customer']) || isset($saleCs['vatValid'])) {
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
        return $this->persistenceHelper->isChanged($sale, ['shipmentMethod', 'shipmentAmount']);
    }

    /**
     * Updates the number.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale number has been update.
     */
    protected function updateNumber(SaleInterface $sale)
    {
        if (0 == strlen($sale->getNumber())) {
            $this->numberGenerator->generate($sale);

            return true;
        }

        return false;
    }

    /**
     * Updates the key.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale key has been updated.
     */
    protected function updateKey(SaleInterface $sale)
    {
        if (0 == strlen($sale->getKey())) {
            $this->keyGenerator->generate($sale);

            return true;
        }

        return false;
    }

    /**
     * Updates the customer information.
     *
     * @param SaleInterface $sale
     * @param bool          $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateInformation(SaleInterface $sale, $persistence = false)
    {
        $changed = false;

        if (null !== $customer = $sale->getCustomer()) {
            // Customer group
            if (null === $sale->getCustomerGroup()) {
                $sale->setCustomerGroup($customer->getCustomerGroup());
                $changed = true;
            }

            // Email
            if (0 == strlen($sale->getEmail())) {
                $sale->setEmail($customer->getEmail());
                $changed = true;
            }

            // Identity
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

            // Company
            if (0 == strlen($sale->getCompany()) && 0 < strlen($customer->getCompany())) {
                $sale->setCompany($customer->getCompany());
                $changed = true;
            }

            // Vat data
            $changed |= $this->updateVatData($sale);

            // Invoice address
            if (null === $sale->getInvoiceAddress() && null !== $address = $customer->getDefaultInvoiceAddress(true)) {
                $changed |= $this->saleUpdater->updateInvoiceAddressFromAddress($sale, $address, $persistence);
            }

            // Delivery address
            if ($sale->isSameAddress()) {
                // Remove unused address
                if (null !== $address = $sale->getDeliveryAddress()) {
                    $sale->setDeliveryAddress(null);
                    if ($persistence) {
                        $this->persistenceHelper->remove($address, true);
                    }
                }
            } else if (null === $sale->getDeliveryAddress() && null !== $address = $customer->getDefaultDeliveryAddress()) {
                $changed |= $this->saleUpdater->updateDeliveryAddressFromAddress($sale, $address, $persistence);
            }
        }

        return $changed;
    }

    /**
     * Updates the vat data.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed.
     */
    protected function updateVatData(SaleInterface $sale)
    {
        $changed = false;

        if (null !== $customer = $sale->getCustomer()) {
            if (0 == strlen($sale->getVatNumber()) && 0 < strlen($customer->getVatNumber())) {
                $sale->setVatNumber($customer->getVatNumber());
                $changed = true;
            }
            if (empty($sale->getVatDetails()) && !empty($customer->getVatDetails())) {
                $sale->setVatDetails($customer->getVatDetails());
                $changed = true;
            }
            if (!$sale->isVatValid() && $customer->isVatValid()) {
                $sale->setVatValid(true);
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * Updates the state.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     *
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
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
     * Prevent forbidden change(s).
     *
     * @param SaleInterface $sale
     *
     * @throws Exception\IllegalOperationException
     */
    protected function preventForbiddenChange(SaleInterface $sale)
    {
        if ($this->persistenceHelper->isChanged($sale, 'customer')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($sale, 'customer');
            if ($old != $new && (0 < $sale->getOutstandingAccepted() || 0 < $sale->getOutstandingExpired())) {
                throw new Exception\IllegalOperationException(
                    "Changing the customer while there is pending outstanding is not yet supported."
                );
            }
        }
    }

    /**
     * Returns the sale from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SaleInterface
     * @throws Exception\InvalidArgumentException
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

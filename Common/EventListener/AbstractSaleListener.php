<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\EventListener;

use DateTime;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Resolver\DueDateResolverInterface;
use Ekyna\Component\Commerce\Pricing\Updater\PricingUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use function is_null;

/**
 * Class AbstractSaleListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleListener
{
    protected PersistenceHelperInterface $persistenceHelper;
    protected GeneratorInterface         $numberGenerator;
    protected GeneratorInterface         $keyGenerator;
    protected PricingUpdaterInterface    $pricingUpdater;
    protected FactoryHelperInterface     $factoryHelper;
    protected SaleUpdaterInterface       $saleUpdater;
    protected DueDateResolverInterface   $dueDateResolver;
    protected StateResolverInterface     $stateResolver;
    protected CurrencyProviderInterface  $currencyProvider;
    protected LocaleProviderInterface    $localeProvider;
    protected AmountCalculatorFactory    $amountCalculatorFactory;
    protected string                     $defaultVatDisplayMode;

    public function setPersistenceHelper(PersistenceHelperInterface $helper): void
    {
        $this->persistenceHelper = $helper;
    }

    public function setNumberGenerator(GeneratorInterface $generator): void
    {
        $this->numberGenerator = $generator;
    }

    public function setKeyGenerator(GeneratorInterface $generator): void
    {
        $this->keyGenerator = $generator;
    }

    public function setPricingUpdater(PricingUpdaterInterface $updater): void
    {
        $this->pricingUpdater = $updater;
    }

    public function setDueDateResolver(DueDateResolverInterface $resolver): void
    {
        $this->dueDateResolver = $resolver;
    }

    public function setFactoryHelper(FactoryHelperInterface $factoryHelper): void
    {
        $this->factoryHelper = $factoryHelper;
    }

    public function setSaleUpdater(SaleUpdaterInterface $updater): void
    {
        $this->saleUpdater = $updater;
    }

    public function setStateResolver(StateResolverInterface $resolver): void
    {
        $this->stateResolver = $resolver;
    }

    public function setCurrencyProvider(CurrencyProviderInterface $provider): void
    {
        $this->currencyProvider = $provider;
    }

    public function setLocaleProvider(LocaleProviderInterface $provider): void
    {
        $this->localeProvider = $provider;
    }

    public function setAmountCalculatorFactory(AmountCalculatorFactory $amountCalculatorFactory): void
    {
        $this->amountCalculatorFactory = $amountCalculatorFactory;
    }

    public function setDefaultVatDisplayMode(string $mode): void
    {
        $this->defaultVatDisplayMode = $mode;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $sale = $this->getSaleFromEvent($event);

        $sale->setContext(null);

        if ($this->handleInsert($sale)) {
            $this->persistenceHelper->persistAndRecompute($sale, false);
        }
    }

    /**
     * Handles the sale insertion.
     *
     * @return bool Whether the sale has been changed.
     */
    protected function handleInsert(SaleInterface $sale): bool
    {
        // Generate number and key
        $changed = $this->updateNumber($sale);
        $changed = $this->updateKey($sale) || $changed;

        // Update customer information
        $changed = $this->updateInformation($sale, true) || $changed;

        // Update pricing
        $changed = $this->pricingUpdater->updateVatNumberSubject($sale) || $changed;

        // Update exchange rate
        $changed = $this->saleUpdater->updateExchangeRate($sale) || $changed;

        // Update payment method
        $changed = $this->saleUpdater->updatePaymentMethod($sale) || $changed;

        // Update outstanding
        $changed = $this->saleUpdater->updatePaymentTerm($sale) || $changed;

        // Update total weight
        $changed = $this->saleUpdater->updateWeightTotal($sale) || $changed;

        // Update shipment method and amount
        $changed = $this->saleUpdater->updateShipmentMethodAndAmount($sale) || $changed;

        // Update discounts
        $changed = $this->saleUpdater->updateDiscounts($sale, true) || $changed;

        // Update taxation
        $changed = $this->saleUpdater->updateTaxation($sale, true) || $changed;

        // Update totals
        $changed = $this->saleUpdater->updateTotals($sale) || $changed;

        // Update state
        $changed = $this->updateState($sale) || $changed;

        // Coupon validity check
        return $this->checkCouponValidity($sale) || $changed;
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $sale = $this->getSaleFromEvent($event);

        $this->preventForbiddenChange($sale);

        $sale->setContext(null);

        if ($this->handleUpdate($sale)) {
            $this->persistenceHelper->persistAndRecompute($sale, false);
        }

        // Schedule content change
        if ($this->persistenceHelper->isChanged($sale, [
            'sample',
            'released',
            'vatDisplayMode',
            'paymentTerm',
            'shipmentAmount',
        ])) {
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
     * @return bool Whether the sale has been changed.
     */
    protected function handleUpdate(SaleInterface $sale): bool
    {
        // Generate number and key
        $changed = $this->updateNumber($sale);
        $changed = $this->updateKey($sale) || $changed;

        // Handle customer information
        $changed = $this->updateInformation($sale, true) || $changed;

        // Update pricing
        if ($this->persistenceHelper->isChanged($sale, 'vatNumber')) {
            $changed = $this->pricingUpdater->updateVatNumberSubject($sale) || $changed;
        }

        // If customer has changed
        if ($this->persistenceHelper->isChanged($sale, 'customer')) {
            // Update payment method
            $changed = $this->saleUpdater->updatePaymentMethod($sale) || $changed;

            // Update payment term
            $changed = $this->saleUpdater->updatePaymentTerm($sale) || $changed;

            // TODO For now customer change is prevented
            /** @see preventForbiddenChange() */

            // TODO Update customer's balances

            // TODO For each payments
            // If payment is paid or has changed from paid state

            // TODO For each (credit)invoices
        }

        // Update shipment and amount
        if ($this->persistenceHelper->isChanged($sale, ['shipmentWeight', 'shipmentMethod', 'customerGroup'])) {
            $changed = $this->saleUpdater->updateShipmentMethodAndAmount($sale) || $changed;
        }

        // Update discounts
        if ($this->persistenceHelper->isChangedFromTo($sale, 'autoDiscount', true, false)) {
            $this->saleUpdater->makeDiscountsMutable($sale);
        } elseif ($this->persistenceHelper->isChangedFromTo($sale, 'autoDiscount', false, true)) {
            $this->saleUpdater->clearMutableDiscounts($sale);
        }

        return $this->updateAdjustments($sale) || $changed;
    }

    public function onAddressChange(ResourceEventInterface $event): void
    {
        $sale = $this->getSaleFromEvent($event);

        if ($this->persistenceHelper->isScheduledForRemove($sale)) {
            $event->stopPropagation();

            return;
        }

        $sale->setContext(null);

        if ($this->handleAddressChange($sale)) {
            $this->persistenceHelper->persistAndRecompute($sale, false);

            $this->scheduleContentChangeEvent($sale);
        }
    }

    /**
     * Handles the address change.
     */
    protected function handleAddressChange(SaleInterface $sale): bool
    {
        $changed = false;

        // Update shipment method and amount
        if ($this->didDeliveryCountryChanged($sale)) {
            $changed = $this->saleUpdater->updateShipmentMethodAndAmount($sale);
        }

        return $this->updateAdjustments($sale) || $changed;
    }

    /**
     * Content (item/adjustment/payment/shipment/invoice) change event handler.
     */
    public function onContentChange(ResourceEventInterface $event): void
    {
        $sale = $this->getSaleFromEvent($event);

        if ($this->persistenceHelper->isScheduledForRemove($sale)) {
            $event->stopPropagation();

            return;
        }

        $sale->setContext(null);

        $this->handleContentChange($sale);

        // Reflect content change on update timestamp
        $sale->setUpdatedAt(new DateTime());

        $this->checkCouponValidity($sale);

        $this->persistenceHelper->persistAndRecompute($sale, false);
    }

    /**
     * Handles the content change.
     */
    protected function handleContentChange(SaleInterface $sale): void
    {
        // Update totals
        $this->saleUpdater->updateWeightTotal($sale);

        // Shipment method and amount
        $this->saleUpdater->updateShipmentMethodAndAmount($sale);

        // Shipment taxation
        if ($this->isShipmentTaxationUpdateNeeded($sale)) {
            $this->saleUpdater->updateShipmentTaxation($sale, true);
        }

        // Update totals
        $this->saleUpdater->updateTotals($sale);

        // TODO Check coupon validity

        // Update state
        $this->updateState($sale);

        // Update due dates
        $this->updateDueDates($sale);
    }

    public function onStateChange(ResourceEventInterface $event): void
    {
        $sale = $this->getSaleFromEvent($event);

        if ($this->persistenceHelper->isScheduledForRemove($sale)) {
            $event->stopPropagation();

            return;
        }

        $sale->setContext(null);

        $this->handleStateChange($sale);
    }

    /**
     * Handles the state change.
     */
    protected function handleStateChange(SaleInterface $sale): void
    {
        if ($this->saleUpdater->updateExchangeRate($sale)) {
            $this->persistenceHelper->persistAndRecompute($sale, false);
        }
    }

    public function onPreCreate(ResourceEventInterface $event): void
    {
        $sale = $this->getSaleFromEvent($event);

        $this->updateInformation($sale);

        $this->pricingUpdater->updateVatNumberSubject($sale);
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        if ($event->getHard()) {
            return;
        }

        if (null === $sale = $this->getSaleFromEvent($event)) {
            return;
        }

        // Stop if sale has valid payments
        foreach ($sale->getPayments() as $payment) {
            if (PaymentStates::isDeletableState($payment->getState())) {
                continue;
            }

            throw new Exception\IllegalOperationException(
                'Sale has valid payments and therefore cannot be deleted.'
            ); // TODO Translation
        }
    }

    /**
     * Returns whether the discount adjustments should be updated.
     */
    protected function isDiscountUpdateNeeded(SaleInterface $sale): bool
    {
        if ($this->persistenceHelper->isChanged($sale, ['autoDiscount', 'couponData', 'customerGroup', 'customer'])) {
            return true;
        }

        if ($sale->getPaidTotal()->isZero()
            && $this->persistenceHelper->isChanged($sale, [
                'customerGroup',
                'customer',
            ])) {
            return true;
        }

        return $this->didInvoiceCountryChanged($sale);
    }

    /**
     * Returns whether the invoice address has changed.
     */
    protected function didInvoiceCountryChanged(SaleInterface $sale): bool
    {
        $saleCs = $this->persistenceHelper->getChangeSet($sale);

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
     * Returns whether the taxation adjustments should be updated.
     */
    protected function isTaxationUpdateNeeded(SaleInterface $sale): bool
    {
        // TODO Get tax resolution mode. (by invoice/delivery/origin).

        if ($this->persistenceHelper->isChanged($sale, ['taxExempt', 'customer', 'vatValid'])) {
            return true;
        }

        return $this->didDeliveryCountryChanged($sale);
    }

    /**
     * Returns whether the delivery country changed.
     */
    protected function didDeliveryCountryChanged(SaleInterface $sale): bool
    {
        $saleCs = $this->persistenceHelper->getChangeSet($sale);

        // Watch for delivery country change
        $oldCountry = $newCountry = null;

        // Resolve the old tax resolution target country
        $oldSameAddress = isset($saleCs['sameAddress']) ? $saleCs['sameAddress'][0] : $sale->isSameAddress();
        if ($oldSameAddress) {
            $oldAddress = isset($saleCs['invoiceAddress']) ? $saleCs['invoiceAddress'][0] : $sale->getInvoiceAddress();
        } else {
            $oldAddress = isset($saleCs['deliveryAddress']) ? $saleCs['deliveryAddress'][0]
                : $sale->getDeliveryAddress();
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
     * Returns whether the shipment related taxation adjustments should be updated.
     */
    protected function isShipmentTaxationUpdateNeeded(SaleInterface $sale): bool
    {
        return $this->persistenceHelper->isChanged($sale, ['shipmentMethod', 'shipmentAmount']);
    }

    /**
     * Updates the number.
     *
     * @return bool Whether the sale number has been update.
     */
    protected function updateNumber(SaleInterface $sale): bool
    {
        if (!empty($sale->getNumber())) {
            return false;
        }

        $sale->setNumber($this->numberGenerator->generate($sale));

        return true;
    }

    /**
     * Updates the key.
     *
     * @return bool Whether the sale key has been updated.
     */
    protected function updateKey(SaleInterface $sale): bool
    {
        if (!empty($sale->getKey())) {
            return false;
        }

        $sale->setKey($this->keyGenerator->generate($sale));

        return true;
    }

    /**
     * Updates the customer information.
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateInformation(SaleInterface $sale, bool $persistence = false): bool
    {
        $changed = false;

        if ($customer = $sale->getCustomer()) {
            // Customer group
            if (null === $sale->getCustomerGroup()) {
                $sale->setCustomerGroup($customer->getCustomerGroup());
                $changed = true;
            }

            // Email
            if (empty($sale->getEmail())) {
                $sale->setEmail($customer->getEmail());
                $changed = true;
            }

            // Identity
            if (empty($sale->getGender())) {
                $sale->setGender($customer->getGender());
                $changed = true;
            }
            if (empty($sale->getFirstName())) {
                $sale->setFirstName($customer->getFirstName());
                $changed = true;
            }
            if (empty($sale->getLastName())) {
                $sale->setLastName($customer->getLastName());
                $changed = true;
            }

            // Company
            if (empty($sale->getCompany()) && !empty($customer->getCompany())) {
                $sale->setCompany($customer->getCompany());
                $changed = true;
            }
            if (empty($sale->getCompanyNumber()) && !empty($customer->getCompanyNumber())) {
                $sale->setCompanyNumber($customer->getCompanyNumber());
                $changed = true;
            }

            // Vat data
            $changed = $this->updateVatData($sale) || $changed;

            // Invoice address
            if (is_null($sale->getInvoiceAddress()) && $address = $customer->getDefaultInvoiceAddress(true)) {
                $changed = $this->saleUpdater->updateInvoiceAddressFromAddress($sale, $address, $persistence)
                    || $changed;
            }

            // Delivery address
            if ($sale->isSameAddress()) {
                // Remove unused address
                if ($address = $sale->getDeliveryAddress()) {
                    $sale->setDeliveryAddress(null);
                    if ($persistence) {
                        $this->persistenceHelper->remove($address, true);
                    }
                }
            } elseif (is_null($sale->getDeliveryAddress()) && $address = $customer->getDefaultDeliveryAddress()) {
                $changed = $this->saleUpdater->updateDeliveryAddressFromAddress($sale, $address, $persistence)
                    || $changed;
            }
        }

        // Vat display mode
        return $this->updateVatDisplayMode($sale) || $changed;
    }

    /**
     * Updates the vat data.
     *
     * @return bool Whether the sale has been changed.
     */
    protected function updateVatData(SaleInterface $sale): bool
    {
        $changed = false;

        if (null !== $customer = $sale->getCustomer()) {
            if (empty($sale->getVatNumber()) && !empty($customer->getVatNumber())) {
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
     * Updates the vat display mode.
     */
    protected function updateVatDisplayMode(SaleInterface $sale): bool
    {
        // Vat display mode must not change if sale has paid payments
        if ($sale->hasPaidPayments()) {
            return false;
        }

        $mode = null;
        if ($group = $sale->getCustomerGroup()) {
            $mode = $group->getVatDisplayMode();
        }
        if (null === $mode) {
            $mode = $this->defaultVatDisplayMode;
        }

        if ($mode !== $sale->getVatDisplayMode()) {
            $sale->setVatDisplayMode($mode);

            return true;
        }

        return false;
    }

    /**
     * Updates the state.
     *
     * @return bool Whether the sale has been changed or not.
     *
     * @throws Exception\CommerceExceptionInterface
     */
    protected function updateState(SaleInterface $sale): bool
    {
        if ($this->stateResolver->resolve($sale)) {
            $this->persistenceHelper->persistAndRecompute($sale, false);

            $this->scheduleStateChangeEvent($sale);

            return true;
        }

        return false;
    }

    /**
     * Updates the due dates.
     *
     * @return bool Whether the sale has been updated.
     */
    protected function updateDueDates(SaleInterface $sale): bool
    {
        $date = $this->dueDateResolver->resolveSaleDueDate($sale);

        if (!DateUtil::equals($date, $sale->getOutstandingDate())) {
            $sale->setOutstandingDate($date);

            return true;
        }

        return false;
    }

    /**
     * Updates taxation and discount adjustments if needed.
     */
    protected function updateAdjustments(SaleInterface $sale): bool
    {
        $changed = false;

        // Update discounts
        if ($this->isDiscountUpdateNeeded($sale)) {
            $changed = $this->saleUpdater->updateDiscounts($sale, true);
        }

        // Update taxation
        if ($this->isTaxationUpdateNeeded($sale)) {
            $changed = $this->saleUpdater->updateTaxation($sale, true) || $changed;
        } elseif ($this->isShipmentTaxationUpdateNeeded($sale)) {
            $changed = $this->saleUpdater->updateShipmentTaxation($sale, true) || $changed;
        }

        return $changed;
    }

    /**
     * Checks that the coupon can still be applied to the given sale.
     * If not, clears the coupon and its data.
     *
     * @return bool Whether the coupon has been cleared.
     */
    protected function checkCouponValidity(SaleInterface $sale): bool
    {
        if (null === $data = $sale->getCouponData()) {
            return false;
        }

        // Don't clear coupon if sale has paid payments
        if ($sale->hasPaidPayments()) {
            return false;
        }

        if (0 < $data['gross']) {
            $result = $this->amountCalculatorFactory->create()->calculateSale($sale);

            if ($data['gross'] > $result->getGross()) {
                $this->clearCoupon($sale);

                return true;
            }
        }

        if (!$data['cumulative'] && $sale->hasDiscountItemAdjustment()) {
            $this->clearCoupon($sale);

            return true;
        }

        return false;
    }

    /**
     * Clears the coupon and its data.
     */
    protected function clearCoupon(SaleInterface $sale): void
    {
        $sale
            ->setCoupon(null)
            ->setCouponData(null);

        $this->saleUpdater->updateDiscounts($sale, true);
    }

    /**
     * Prevent forbidden change(s).
     *
     * @throws Exception\IllegalOperationException
     */
    protected function preventForbiddenChange(SaleInterface $sale): void
    {
        // Prevent currency change if exchange rate is defined
        if ($this->persistenceHelper->isChanged($sale, 'currency')) {
            if ($sale->getExchangeRate()) {
                throw new Exception\IllegalOperationException(
                    'Changing the currency while exchange rate is set is not yet supported.'
                );
            }
        }

        // Prevent customer change if outstanding accepted or expired
        if ($this->persistenceHelper->isChanged($sale, 'customer')) {
            [$old, $new] = $this->persistenceHelper->getChangeSet($sale, 'customer');
            if ($old != $new && (0 < $sale->getOutstandingAccepted() || 0 < $sale->getOutstandingExpired())) {
                throw new Exception\IllegalOperationException(
                    'Changing the customer while there is pending outstanding is not yet supported.'
                );
            }
        }
    }

    /**
     * Returns the sale from the event.
     */
    abstract protected function getSaleFromEvent(ResourceEventInterface $event): SaleInterface;

    /**
     * Schedule the content change event handler.
     */
    abstract protected function scheduleContentChangeEvent(SaleInterface $sale): void;

    /**
     * Schedule the state change event handler.
     */
    abstract protected function scheduleStateChangeEvent(SaleInterface $sale): void;
}

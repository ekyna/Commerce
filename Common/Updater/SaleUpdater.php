<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Builder\AddressBuilderInterface;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Releaser\ReleaserInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\InStore\InStorePlatform;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;

/**
 * Class SaleUpdater
 * @package Ekyna\Component\Commerce\Common\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleUpdater implements SaleUpdaterInterface
{
    /**
     * @var AddressBuilderInterface
     */
    protected $addressBuilder;

    /**
     * @var AdjustmentBuilderInterface
     */
    protected $adjustmentBuilder;

    /**
     * @var AmountCalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var WeightCalculatorInterface
     */
    protected $weightCalculator;

    /**
     * @var ShipmentPriceResolverInterface
     */
    protected $shipmentPriceResolver;

    /**
     * @var PaymentCalculatorInterface
     */
    protected $paymentCalculator;

    /**
     * @var InvoiceSubjectCalculatorInterface
     */
    protected $invoiceCalculator;

    /**
     * @var ReleaserInterface
     */
    protected $outstandingReleaser;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param AddressBuilderInterface           $addressBuilder
     * @param AdjustmentBuilderInterface        $adjustmentBuilder
     * @param AmountCalculatorFactory           $calculatorFactory
     * @param CurrencyConverterInterface        $currencyConverter
     * @param WeightCalculatorInterface         $weightCalculator
     * @param ShipmentPriceResolverInterface    $shipmentPriceResolver
     * @param PaymentCalculatorInterface        $paymentCalculator
     * @param InvoiceSubjectCalculatorInterface $invoiceCalculator
     * @param ReleaserInterface                 $outstandingReleaser
     * @param SaleFactoryInterface              $saleFactory
     */
    public function __construct(
        AddressBuilderInterface $addressBuilder,
        AdjustmentBuilderInterface $adjustmentBuilder,
        AmountCalculatorFactory $calculatorFactory,
        CurrencyConverterInterface $currencyConverter,
        WeightCalculatorInterface $weightCalculator,
        ShipmentPriceResolverInterface $shipmentPriceResolver,
        PaymentCalculatorInterface $paymentCalculator,
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        ReleaserInterface $outstandingReleaser,
        SaleFactoryInterface $saleFactory
    ) {
        $this->addressBuilder = $addressBuilder;
        $this->adjustmentBuilder = $adjustmentBuilder;
        $this->calculatorFactory = $calculatorFactory;
        $this->currencyConverter = $currencyConverter;
        $this->weightCalculator = $weightCalculator;
        $this->shipmentPriceResolver = $shipmentPriceResolver;
        $this->paymentCalculator = $paymentCalculator;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->outstandingReleaser = $outstandingReleaser;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritDoc
     */
    public function recalculate(SaleInterface $sale): bool
    {
        $changed = false;

        // 1. discounts
        if (0 == $sale->getPaidTotal()) { // Do not update if paid amount
            $changed = $this->updateDiscounts($sale);
        }

        // 2. weight
        $changed |= $this->updateWeightTotal($sale);

        // 3. shipment amount (based on 2.)
        $changed |= $this->updateShipmentMethodAndAmount($sale);

        // 4. taxation (items and shipment)
        $changed |= $this->updateTaxation($sale);

        // 5. totals (content, payments and invoices)
        $changed |= $this->updateTotals($sale);

        // TODO item count

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function updateWeightTotal(SaleInterface $sale): bool
    {
        $weightTotal = $this->weightCalculator->calculateSale($sale);

        if ($sale->getWeightTotal() != $weightTotal) {
            $sale->setWeightTotal($weightTotal);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function updateTotals(SaleInterface $sale): bool
    {
        $changed = $this->updateAmountTotals($sale);

        $changed |= $this->updatePaymentTotals($sale);

        $changed |= $this->updateInvoiceTotals($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function updateInvoiceAddressFromAddress(
        SaleInterface $sale,
        AddressInterface $source,
        $persistence = false
    ): bool {
        return $this->addressBuilder->buildSaleInvoiceAddressFromAddress($sale, $source, $persistence);
    }

    /**
     * @inheritDoc
     */
    public function updateDeliveryAddressFromAddress(
        SaleInterface $sale,
        AddressInterface $source,
        $persistence = false
    ): bool {
        return $this->addressBuilder->buildSaleDeliveryAddressFromAddress($sale, $source, $persistence);
    }

    /**
     * @inheritDoc
     */
    public function updateDiscounts(SaleInterface $sale, $persistence = false): bool
    {
        $changed = $this->adjustmentBuilder->buildDiscountAdjustmentsForSaleItems($sale, $persistence);

        return $this->adjustmentBuilder->buildDiscountAdjustmentsForSale($sale, $persistence) || $changed;
    }

    /**
     * @inheritDoc
     */
    public function updateTaxation(SaleInterface $sale, $persistence = false): bool
    {
        $changed = $this->adjustmentBuilder->buildTaxationAdjustmentsForSaleItems($sale, $persistence);

        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, $persistence) || $changed;
    }

    /**
     * @inheritDoc
     */
    public function updateShipmentTaxation(SaleInterface $sale, $persistence = false): bool
    {
        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, $persistence);
    }

    /**
     * @inheritDoc
     */
    public function updateShipmentMethodAndAmount(SaleInterface $sale): bool
    {
        // Abort if sale auto shipping is disabled
        if (!$sale->isAutoShipping()) {
            return false;
        }

        // Abort if sale has payment with non deletable state.
        foreach ($sale->getPayments() as $payment) {
            if (!PaymentStates::isDeletableState($payment->getState())) {
                return false;
            }
        }

        $updated = false;
        $prices = $this->shipmentPriceResolver->getAvailablePricesBySale($sale);

        // Assert that the sale's shipment method is still available
        if (null !== $initialMethod = $sale->getShipmentMethod()) {
            $found = false;
            foreach ($prices as $price) {
                if ($price->getMethod() === $initialMethod) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $sale->setShipmentMethod(null);
                $updated = true;
            }
        }

        // If sale does not have a shipment method, set the cheapest one
        if (null === $method = $sale->getShipmentMethod()) {
            $price = false;
            if (!empty($prices)) {
                // Find the cheapest non 'in store' shipment method
                foreach ($prices as $price) {
                    if ($price->getMethod()->getPlatformName() !== InStorePlatform::NAME) {
                        break;
                    }
                }
                // Not found, pick the first one
                if (false === $price) {
                    $price = reset($prices);
                }
            }
            if ($price) {
                $sale->setShipmentMethod($method = $price->getMethod());
                $updated = true;
            } else {
                // Revert to initial method
                $sale->setShipmentMethod($method = $initialMethod);
                $updated = false;
            }
        }

        // Resolve shipping cost
        $amount = 0;
        if (null !== $method) {
            if (null !== $price = $this->shipmentPriceResolver->getPriceBySale($sale)) {
                $amount = $price->isFree() ? 0 : $price->getPrice();
            }
        }

        // Update sale's shipping cost if needed
        if ($amount != $sale->getShipmentAmount()) {
            $sale->setShipmentAmount($amount);
            $updated = true;
        }

        return $updated;
    }

    /**
     * @inheritDoc
     */
    public function updatePaymentTerm(SaleInterface $sale): bool
    {
        // Don't override payment term if set
        if (null !== $sale->getPaymentTerm()) {
            return false;
        }

        $term = null;
        if (null !== $customer = $sale->getCustomer()) {
            // From parent if available
            if ($customer->hasParent()) {
                $term = $customer->getParent()->getPaymentTerm();
            } else {
                $term = $customer->getPaymentTerm();
            }
        }

        if ($term !== $sale->getPaymentTerm()) {
            $sale->setPaymentTerm($term);

            return true;
        }

        return false;
    }

    /**
     * Updates the payment method.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed.
     */
    public function updatePaymentMethod(SaleInterface $sale): bool
    {
        $method = null;

        if ($customer = $sale->getCustomer()) {
            if ($customer->hasParent()) {
                $customer = $customer->getParent();
            }

            $method = $customer->getDefaultPaymentMethod();
        }

        $current = $sale->getPaymentMethod();
        if ($method !== $current) {
            if ($customer && !$method) {
                return false;
            }

            $sale->setPaymentMethod($method);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function updateExchangeRate(SaleInterface $sale, bool $force = false): bool
    {
        // If sale is not accepted
        if (is_null($sale->getAcceptedAt()) && !$force) {
            // Don't change exchange rate/date

            return false;
        }

        // (Sale is accepted)

        // If exchange rate is defined
        if (null !== $sale->getExchangeRate()) {
            return false;
        }

        // Get exchange rate from the first paid payment
        foreach ($sale->getPayments(true) as $payment) {
            // Skip not paid
            if (!PaymentStates::isPaidState($payment->getState())) {
                continue;
            }

            if (is_null($payment->getExchangeRate())) {
                continue;
            }

            $sale->setExchangeDate($payment->getExchangeDate());

            if ($payment->getCurrency()->getCode() === $sale->getCurrency()->getCode()) {
                $sale->setExchangeRate($payment->getExchangeRate());

                return true;
            }

            break;
        }

        return $this->currencyConverter->setSubjectExchangeRate($sale);
    }

    /**
     * @inheritDoc
     */
    public function updateAmountTotals(SaleInterface $sale): bool
    {
        $changed = false;

        $result = $this->calculatorFactory->create()->calculateSale($sale);

        if (0 !== Money::compare($result->getBase(), $sale->getNetTotal(), 5)) {
            $sale->setNetTotal($result->getBase());
            $changed = true;
        }

        if (0 !== Money::compare($result->getTotal(), $sale->getGrandTotal(), 5)) {
            $sale->setGrandTotal($result->getTotal());
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function updatePaymentTotals(SaleInterface $sale): bool
    {
        $changed = false;

        $currency = $sale->getCurrency()->getCode();

        // Update paid total if needed
        $paid = $this->paymentCalculator->calculatePaidTotal($sale);
        if (0 != Money::compare($paid, $sale->getPaidTotal(), $currency)) {
            $sale->setPaidTotal($paid);
            $changed = true;
        }
        // Update refunded total if needed
        $refunded = $this->paymentCalculator->calculateRefundedTotal($sale);
        if (0 != Money::compare($refunded, $sale->getRefundedTotal(), $currency)) {
            $sale->setRefundedTotal($refunded);
            $changed = true;
        }
        // Update pending total total if needed
        $pending = $this->paymentCalculator->calculateOfflinePendingTotal($sale);
        if (0 != Money::compare($pending, $sale->getPendingTotal(), $currency)) {
            $sale->setPendingTotal($pending);
            $changed = true;
        }
        // Update accepted outstanding total if needed
        $acceptedOutstanding = $this->paymentCalculator->calculateOutstandingAcceptedTotal($sale);
        if (0 != Money::compare($acceptedOutstanding, $sale->getOutstandingAccepted(), $currency)) {
            $sale->setOutstandingAccepted($acceptedOutstanding);
            $changed = true;
        }
        // Update expired outstanding total if needed
        $expiredOutstanding = $this->paymentCalculator->calculateOutstandingExpiredTotal($sale);
        if (0 != Money::compare($expiredOutstanding, $sale->getOutstandingExpired(), $currency)) {
            $sale->setOutstandingExpired($expiredOutstanding);
            $changed = true;
        }

        // If payment totals has changed and fund has been released
        if ($changed && $this->outstandingReleaser->releaseFund($sale)) {
            // Re-update the outstanding totals
            $sale->setOutstandingAccepted($this->paymentCalculator->calculateOutstandingAcceptedTotal($sale));
            $sale->setOutstandingExpired($this->paymentCalculator->calculateOutstandingExpiredTotal($sale));
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function updateInvoiceTotals(SaleInterface $sale): bool
    {
        if (!$sale instanceof InvoiceSubjectInterface) {
            return false;
        }

        $changed = false;

        $invoice = $this->invoiceCalculator->calculateInvoiceTotal($sale);
        if (0 != Money::compare($invoice, $sale->getInvoiceTotal(), $sale->getCurrency()->getCode())) {
            $sale->setInvoiceTotal($invoice);

            $changed = true;
        }

        $credit = $this->invoiceCalculator->calculateCreditTotal($sale);
        if (0 != Money::compare($credit, $sale->getCreditTotal(), $sale->getCurrency()->getCode())) {
            $sale->setCreditTotal($credit);

            $changed = true;
        }

        return $changed;
    }
}

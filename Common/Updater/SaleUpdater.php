<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Updater;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Builder\AddressBuilderInterface;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
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
    protected AddressBuilderInterface           $addressBuilder;
    protected AdjustmentBuilderInterface        $adjustmentBuilder;
    protected AmountCalculatorFactory           $calculatorFactory;
    protected CurrencyConverterInterface        $currencyConverter;
    protected WeightCalculatorInterface         $weightCalculator;
    protected ShipmentPriceResolverInterface    $shipmentPriceResolver;
    protected PaymentCalculatorInterface        $paymentCalculator;
    protected InvoiceSubjectCalculatorInterface $invoiceCalculator;
    protected ReleaserInterface                 $outstandingReleaser;
    protected FactoryHelperInterface            $factoryHelper;
    protected string                            $defaultCurrency;

    public function __construct(
        AddressBuilderInterface           $addressBuilder,
        AdjustmentBuilderInterface        $adjustmentBuilder,
        AmountCalculatorFactory           $calculatorFactory,
        CurrencyConverterInterface        $currencyConverter,
        WeightCalculatorInterface         $weightCalculator,
        ShipmentPriceResolverInterface    $shipmentPriceResolver,
        PaymentCalculatorInterface        $paymentCalculator,
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        ReleaserInterface                 $outstandingReleaser,
        FactoryHelperInterface            $factoryHelper
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
        $this->factoryHelper = $factoryHelper;
    }

    public function recalculate(SaleInterface $sale): bool
    {
        $changed = false;

        // 1. discounts
        if ($sale->getPaidTotal()->isZero()) { // Do not update if paid amount
            $changed = $this->updateDiscounts($sale);
        }

        // 2. weight
        $changed = $this->updateWeightTotal($sale) || $changed;

        // 3. shipment amount (based on 2.)
        $changed = $this->updateShipmentMethodAndAmount($sale) || $changed;

        // 4. taxation (items and shipment)
        $changed = $this->updateTaxation($sale) || $changed;

        // 5. totals (content, payments and invoices)
        $changed = $this->updateTotals($sale) || $changed;

        // TODO item count

        return $changed;
    }

    public function updateWeightTotal(SaleInterface $sale): bool
    {
        $weightTotal = $this->weightCalculator->calculateSale($sale);

        if (!$sale->getWeightTotal()->equals($weightTotal)) {
            $sale->setWeightTotal($weightTotal);

            return true;
        }

        return false;
    }

    public function updateTotals(SaleInterface $sale): bool
    {
        $changed = $this->updateAmountTotals($sale);

        $changed = $this->updatePaymentTotals($sale) || $changed;

        return $this->updateInvoiceTotals($sale) || $changed;
    }

    public function updateInvoiceAddressFromAddress(
        SaleInterface    $sale,
        AddressInterface $source,
        bool             $persistence = false
    ): bool {
        return $this->addressBuilder->buildSaleInvoiceAddressFromAddress($sale, $source, $persistence);
    }

    public function updateDeliveryAddressFromAddress(
        SaleInterface    $sale,
        AddressInterface $source,
        bool             $persistence = false
    ): bool {
        return $this->addressBuilder->buildSaleDeliveryAddressFromAddress($sale, $source, $persistence);
    }

    public function updateDiscounts(SaleInterface $sale, bool $persistence = false): bool
    {
        /* TODO (?) if ($sale->getPaidTotal()->isZero()) {
            return false;
        }*/

        $changed = $this->adjustmentBuilder->buildDiscountAdjustmentsForSaleItems($sale, $persistence);

        return $this->adjustmentBuilder->buildDiscountAdjustmentsForSale($sale, $persistence) || $changed;
    }

    public function updateTaxation(SaleInterface $sale, bool $persistence = false): bool
    {
        $changed = $this->adjustmentBuilder->buildTaxationAdjustmentsForSaleItems($sale, $persistence);

        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, $persistence) || $changed;
    }

    public function updateShipmentTaxation(SaleInterface $sale, bool $persistence = false): bool
    {
        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, $persistence);
    }

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
        if ($initialMethod = $sale->getShipmentMethod()) {
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
        $price = null;
        if ($method && $price = $this->shipmentPriceResolver->getPriceBySale($sale)) {
            $price = $price->getPrice();
        }
        if (null === $price) {
            $price = new Decimal(0);
        }

        // Update sale's shipping cost if needed
        if (!$price->equals($sale->getShipmentAmount())) {
            $sale->setShipmentAmount($price);
            $updated = true;
        }

        return $updated;
    }

    public function updatePaymentTerm(SaleInterface $sale): bool
    {
        // Don't override payment term if set
        if (null !== $sale->getPaymentTerm()) {
            return false;
        }

        $term = null;
        if ($customer = $sale->getCustomer()) {
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

    public function updateExchangeRate(SaleInterface $sale, bool $force = false): bool
    {
        // If sale is not accepted
        if (is_null($sale->getAcceptedAt()) && !$force) {
            // Don't change exchange rate/date

            return false;
        }

        // (Sale is accepted)

        // If exchange rate is defined
        if ($sale->getExchangeRate()) {
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

    public function updateAmountTotals(SaleInterface $sale): bool
    {
        $changed = false;

        $result = $this->calculatorFactory->create()->calculateSale($sale);

        if (!$sale->getNetTotal()->equals($result->getBase())) {
            $sale->setNetTotal($result->getBase());
            $changed = true;
        }

        if (!$sale->getGrandTotal()->equals($result->getTotal())) {
            $sale->setGrandTotal($result->getTotal());
            $changed = true;
        }

        return $changed;
    }

    public function updatePaymentTotals(SaleInterface $sale): bool
    {
        $changed = false;

        // Update paid total if needed
        $paid = $this->paymentCalculator->calculatePaidTotal($sale);
        if (!$sale->getPaidTotal()->equals($paid)) {
            $sale->setPaidTotal($paid);
            $changed = true;
        }
        // Update refunded total if needed
        $refunded = $this->paymentCalculator->calculateRefundedTotal($sale);
        if (!$sale->getRefundedTotal()->equals($refunded)) {
            $sale->setRefundedTotal($refunded);
            $changed = true;
        }
        // Update pending total total if needed
        $pending = $this->paymentCalculator->calculateOfflinePendingTotal($sale);
        if (!$sale->getPendingTotal()->equals($pending)) {
            $sale->setPendingTotal($pending);
            $changed = true;
        }
        // Update accepted outstanding total if needed
        $acceptedOutstanding = $this->paymentCalculator->calculateOutstandingAcceptedTotal($sale);
        if (!$sale->getOutstandingAccepted()->equals($acceptedOutstanding)) {
            $sale->setOutstandingAccepted($acceptedOutstanding);
            $changed = true;
        }
        // Update expired outstanding total if needed
        $expiredOutstanding = $this->paymentCalculator->calculateOutstandingExpiredTotal($sale);
        if (!$sale->getOutstandingExpired()->equals($expiredOutstanding)) {
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

    public function updateInvoiceTotals(SaleInterface $sale): bool
    {
        if (!$sale instanceof InvoiceSubjectInterface) {
            return false;
        }

        $changed = false;

        $invoice = $this->invoiceCalculator->calculateInvoiceTotal($sale);
        if (!$sale->getInvoiceTotal()->equals($invoice)) {
            $sale->setInvoiceTotal($invoice);

            $changed = true;
        }

        $credit = $this->invoiceCalculator->calculateCreditTotal($sale);
        if (!$sale->getCreditTotal()->equals($credit)) {
            $sale->setCreditTotal($credit);

            $changed = true;
        }

        return $changed;
    }
}

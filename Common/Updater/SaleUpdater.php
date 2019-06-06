<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Builder\AddressBuilderInterface;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
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
     * @var AmountCalculatorInterface
     */
    protected $amountCalculator;

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
     * @var InvoiceCalculatorInterface
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
     * Constructor.
     *
     * @param AddressBuilderInterface        $addressBuilder
     * @param AdjustmentBuilderInterface     $adjustmentBuilder
     * @param AmountCalculatorInterface      $amountCalculator
     * @param WeightCalculatorInterface      $weightCalculator
     * @param ShipmentPriceResolverInterface $shipmentPriceResolver
     * @param PaymentCalculatorInterface     $paymentCalculator
     * @param InvoiceCalculatorInterface     $invoiceCalculator
     * @param ReleaserInterface              $outstandingReleaser
     * @param SaleFactoryInterface           $saleFactory
     */
    public function __construct(
        AddressBuilderInterface $addressBuilder,
        AdjustmentBuilderInterface $adjustmentBuilder,
        AmountCalculatorInterface $amountCalculator,
        WeightCalculatorInterface $weightCalculator,
        ShipmentPriceResolverInterface $shipmentPriceResolver,
        PaymentCalculatorInterface $paymentCalculator,
        InvoiceCalculatorInterface $invoiceCalculator,
        ReleaserInterface $outstandingReleaser,
        SaleFactoryInterface $saleFactory
    ) {
        $this->addressBuilder = $addressBuilder;
        $this->adjustmentBuilder = $adjustmentBuilder;
        $this->amountCalculator = $amountCalculator;
        $this->weightCalculator = $weightCalculator;
        $this->shipmentPriceResolver = $shipmentPriceResolver;
        $this->paymentCalculator = $paymentCalculator;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->outstandingReleaser = $outstandingReleaser;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function recalculate(SaleInterface $sale, $force = false)
    {
        $changed = false;

        // TODO Test
        // 1. discounts
        if (0 == $sale->getPaidTotal()) { // Do not update if paid amount
            $changed = $this->updateDiscounts($sale);
        }
        // 2. weight total
        $changed |= $this->updateWeightTotal($sale);
        // 3. shipment amount (based on 2.)
        $changed |= $this->updateShipmentMethodAndAmount($sale);
        // 4. taxation (items and shipment)
        $changed |= $this->updateTaxation($sale);
        // 5. net and grand totals
        $changed |= $this->updateAmountsTotal($sale);
        // 6. paid and outstanding totals
        $changed |= $this->updatePaymentTotal($sale);
        // 7. invoice total
        $changed |= $this->updateInvoiceTotal($sale);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateTotals(SaleInterface $sale)
    {
        $changed = $this->updateWeightTotal($sale);

        $changed |= $this->updateAmountsTotal($sale);

        $changed |= $this->updatePaymentTotal($sale);

        $changed |= $this->updateInvoiceTotal($sale);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateInvoiceAddressFromAddress(
        SaleInterface $sale,
        AddressInterface $source,
        $persistence = false
    ) {
        return $this->addressBuilder->buildSaleInvoiceAddressFromAddress($sale, $source, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function updateDeliveryAddressFromAddress(
        SaleInterface $sale,
        AddressInterface $source,
        $persistence = false
    ) {
        return $this->addressBuilder->buildSaleDeliveryAddressFromAddress($sale, $source, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function updateDiscounts(SaleInterface $sale, $persistence = false)
    {
        $changed = $this->adjustmentBuilder->buildDiscountAdjustmentsForSaleItems($sale, $persistence);

        return $this->adjustmentBuilder->buildDiscountAdjustmentsForSale($sale, $persistence) || $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateTaxation(SaleInterface $sale, $persistence = false)
    {
        $changed = $this->adjustmentBuilder->buildTaxationAdjustmentsForSaleItems($sale, $persistence);

        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, $persistence) || $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateShipmentTaxation(SaleInterface $sale, $persistence = false)
    {
        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function updateShipmentMethodAndAmount(SaleInterface $sale)
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

        // If sale does not have a shipment method, set the cheaper one
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
     * @inheritdoc
     */
    public function updatePaymentTerm(SaleInterface $sale)
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
     * Updates the total weight.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateWeightTotal(SaleInterface $sale)
    {
        $weightTotal = $this->weightCalculator->calculateSale($sale);

        if ($sale->getWeightTotal() != $weightTotal) {
            $sale->setWeightTotal($weightTotal);

            return true;
        }

        return false;
    }

    /**
     * Updates the sale's net and grand totals.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateAmountsTotal(SaleInterface $sale)
    {
        $changed = false;

        $currency = $sale->getCurrency()->getCode();

        $sale->clearResults();

        $result = $this->amountCalculator->calculateSale($sale);

        if (0 != Money::compare($result->getBase(), $sale->getNetTotal(), $currency)) {
            $sale->setNetTotal($result->getBase());
            $changed = true;
        }

        if (0 != Money::compare($result->getTotal(), $sale->getGrandTotal(), $currency)) {
            $sale->setGrandTotal($result->getTotal());
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the payment totals total.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updatePaymentTotal(SaleInterface $sale)
    {
        $changed = false;

        $currency = $sale->getCurrency()->getCode();

        // Update paid total if needed
        $paid = $this->paymentCalculator->calculatePaidTotal($sale);
        if (0 != Money::compare($paid, $sale->getPaidTotal(), $currency)) {
            $sale->setPaidTotal($paid);
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
            //$sale->setPaidTotal($this->paymentCalculator->calculatePaidTotal($sale));
            //$sale->setPendingTotal($this->paymentCalculator->calculateOfflinePendingTotal($sale));
            $sale->setOutstandingAccepted($this->paymentCalculator->calculateOutstandingAcceptedTotal($sale));
            $sale->setOutstandingExpired($this->paymentCalculator->calculateOutstandingExpiredTotal($sale));
        }

        return $changed;
    }

    /**
     * Updates the sale invoice total.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateInvoiceTotal(SaleInterface $sale)
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

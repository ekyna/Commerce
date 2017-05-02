<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Builder\AddressBuilderInterface;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Releaser\ReleaserInterface;
use Ekyna\Component\Commerce\Payment\Util\PaymentUtil;

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
     * @var AmountsCalculatorInterface
     */
    protected $amountCalculator;

    /**
     * @var WeightCalculatorInterface
     */
    protected $weightCalculator;

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
     * @param AddressBuilderInterface    $addressBuilder
     * @param AdjustmentBuilderInterface $adjustmentBuilder
     * @param AmountsCalculatorInterface $amountCalculator
     * @param WeightCalculatorInterface  $weightCalculator
     * @param ReleaserInterface        $outstandingReleaser
     * @param SaleFactoryInterface       $saleFactory
     */
    public function __construct(
        AddressBuilderInterface $addressBuilder,
        AdjustmentBuilderInterface $adjustmentBuilder,
        AmountsCalculatorInterface $amountCalculator,
        WeightCalculatorInterface $weightCalculator,
        ReleaserInterface $outstandingReleaser,
        SaleFactoryInterface $saleFactory
    ) {
        $this->addressBuilder = $addressBuilder;
        $this->adjustmentBuilder = $adjustmentBuilder;
        $this->amountCalculator = $amountCalculator;
        $this->weightCalculator = $weightCalculator;
        $this->outstandingReleaser = $outstandingReleaser;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function recalculate(SaleInterface $sale, $force = false)
    {
        // TODO Test
        // 1. discounts
        $changed = $this->updateDiscounts($sale);
        // 2. weight total
        $changed |= $this->updateTotalWeight($sale);
        // 3. shipment amount (based on 2.)
        //TODO$changed |= $this->updateTotalWeight($sale);
        // 4. taxation (items and shipment)
        $changed |= $this->updateTaxation($sale);
        // 5. gross and gran totals
        $changed |= $this->updateTotals($sale);

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
        return $this->addressBuilder->buildSaleInvoiceAddressFromAddress($sale, $source, true);
    }

    /**
     * @inheritdoc
     */
    public function updateDeliveryAddressFromAddress(
        SaleInterface $sale,
        AddressInterface $source,
        $persistence = false
    ) {
        return $this->addressBuilder->buildSaleDeliveryAddressFromAddress($sale, $source, true);
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
    public function updateTotalWeight(SaleInterface $sale)
    {
        $weightTotal = $this->weightCalculator->calculateSale($sale);

        if ($sale->getWeightTotal() != $weightTotal) {
            $sale->setWeightTotal($weightTotal);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateOutstandingAndTerm(SaleInterface $sale)
    {
        $changed = false;

        $term = null;

        // Payment term
        if (null !== $customer = $sale->getCustomer()) {
            $term = $customer->getPaymentTerm();
        }
        if ($term !== $sale->getPaymentTerm()) {
            $sale->setPaymentTerm($term);
        }

        // Outstanding payments
        $hasOutstandingPayments = false;
        foreach ($sale->getPayments() as $payment) {
            if ($payment->getMethod()->isOutstanding() && PaymentStates::isPaidState($payment->getState())) {
                $hasOutstandingPayments = true;
                break;
            }
        }

        // If has payment term and outstanding payments
        if ($term && $hasOutstandingPayments) {
            // Update outstanding date
            $date = PaymentUtil::calculateOutstandingDate($term, $sale->getCreatedAt());
            if ($date !== $sale->getOutstandingDate()) {
                $sale->setOutstandingDate($date);
                $changed = true;
            }
        } elseif (null !== $sale->getOutstandingDate()) {
            $sale->setOutstandingDate(null);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updatePaidTotal(SaleInterface $sale)
    {
        // Paid total calculator
        $calculate = function(SaleInterface $sale) {
            $total = 0;

            foreach ($sale->getPayments() as $payment) {
                if (PaymentStates::isPaidState($payment->getState())) {
                    $total += $payment->getAmount();
                }
            }

            return $total;
        };

        $total = $calculate($sale);
        if ($total != $sale->getPaidTotal()) {
            $sale->setPaidTotal($total);

            // Paid total has changed => try to release outstanding fund
            if ($this->outstandingReleaser->releaseFund($sale)) {
                // Re-update the paid total
                $sale->setPaidTotal($calculate($sale));
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateTotalAmounts(SaleInterface $sale)
    {
        $changed = false;

        $amounts = $this->amountCalculator->calculateSale($sale);

        if ($sale->getNetTotal() != $amounts->getBase()) {
            $sale->setNetTotal($amounts->getBase());
            $changed = true;
        }

        if ($sale->getGrandTotal() != $amounts->getTotal()) {
            $sale->setGrandTotal($amounts->getTotal());
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateTotals(SaleInterface $sale)
    {
        $changed = $this->updateTotalAmounts($sale);

        $changed |= $this->updateTotalWeight($sale);

        $changed |= $this->updatePaidTotal($sale);

        return $changed;
    }
}

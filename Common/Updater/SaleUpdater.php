<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Builder\AddressBuilderInterface;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\AddressUtil;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

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
     * @param SaleFactoryInterface       $saleFactory
     */
    public function __construct(
        AddressBuilderInterface    $addressBuilder,
        AdjustmentBuilderInterface $adjustmentBuilder,
        AmountsCalculatorInterface $amountCalculator,
        WeightCalculatorInterface $weightCalculator,
        SaleFactoryInterface $saleFactory
    ) {
        $this->addressBuilder = $addressBuilder;
        $this->adjustmentBuilder = $adjustmentBuilder;
        $this->amountCalculator = $amountCalculator;
        $this->weightCalculator = $weightCalculator;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function recalculate(SaleInterface $sale, $force = false)
    {
        // TODO
        // 1. discounts
        // 2. weight total
        // 3. shipment amount (based on 2.)
        // 4. taxation (items and shipment)
        // 5. gross and gran totals

        $changed = $this->updateTaxation($sale);

        return $this->updateTotals($sale) || $changed;
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
    public function updatePaidTotal(SaleInterface $sale)
    {
        $total = 0;

        foreach ($sale->getPayments() as $payment) {
            if (PaymentStates::isPaidState($payment->getState())) {
                $total += $payment->getAmount();
            }
        }

        if ($total != $sale->getPaidTotal()) {
            $sale->setPaidTotal($total);

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

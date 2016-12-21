<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Class SaleUpdater
 * @package Ekyna\Component\Commerce\Common\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleUpdater implements SaleUpdaterInterface
{
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
     * Constructor.
     *
     * @param AdjustmentBuilderInterface $adjustmentBuilder
     * @param AmountsCalculatorInterface $amountCalculator
     * @param WeightCalculatorInterface  $weightCalculator
     */
    public function __construct(
        AdjustmentBuilderInterface $adjustmentBuilder,
        AmountsCalculatorInterface $amountCalculator,
        WeightCalculatorInterface $weightCalculator
    ) {
        $this->adjustmentBuilder = $adjustmentBuilder;
        $this->amountCalculator = $amountCalculator;
        $this->weightCalculator = $weightCalculator;
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

        return $this->updateTotalWeight($sale) || $changed;
    }
}

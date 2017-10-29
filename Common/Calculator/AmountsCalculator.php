<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class AmountsCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AmountsCalculator implements AmountsCalculatorInterface
{
    /**
     * @var string
     */
    private $mode = self::MODE_NET;


    /**
     * @inheritdoc
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function calculateSale(SaleInterface $sale, $gross = false)
    {
        $result = new Result(); // TODO Currency's precision

        // Items result
        if ($sale->hasItems()) {
            foreach ($sale->getItems() as $item) {
                $result->merge($this->calculateSaleItem($item));
            }
        }

        // Discount adjustments results
        if (!$gross && $sale->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            $adjustments = $sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT);
            foreach ($adjustments as $adjustment) {
                $result->merge($this->calculateDiscountAdjustment($adjustment));
            }
        }

        // Shipment result
        if (!$gross) {
            $result->merge($this->calculateShipment($sale));
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateShipment(SaleInterface $sale)
    {
        $result = new Result(); // TODO Currency's precision

        if (0 < $base = $sale->getShipmentAmount()) {
            $base = $this->round($base);

            $result->addBase($base);

            $this->addTaxation($result, $sale, $base);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleItem(SaleItemInterface $item, $gross = false, $single = false)
    {
        $result = new Result(); // TODO Currency's precision

        if (!$item->isCompound()) {
            $base = $this->mode === self::MODE_NET
                ? $this->round($item->getNetPrice())
                : $item->getNetPrice();

            $result->addBase($base);

            $this->addTaxation($result, $item, $base);

            $result->multiply($item->getTotalQuantity());
        }

        if ($item->hasChildren() && !$single) { // Calculate as a "parent" item
            // Merge children results
            foreach ($item->getChildren() as $child) {
                // Item result must take account of child's discounts, so not gross
                $result->merge($this->calculateSaleItem($child));
            }
        }

        // Discount adjustments
        if (!$gross && $item->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            $adjustments = $item->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT);
            foreach ($adjustments as $adjustment) {
                // Only 'percent' mode adjustments are allowed here.
                $this->assertAdjustmentMode($adjustment, AdjustmentModes::MODE_PERCENT);

                $result->merge($this->calculateDiscountAdjustment($adjustment));
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateDiscountAdjustment(AdjustmentInterface $adjustment, Result $parentResult = null)
    {
        $this->assertAdjustmentType($adjustment, AdjustmentTypes::TYPE_DISCOUNT);

        if (null === $parentResult) {
            $adjustable = $adjustment->getAdjustable();
            if ($adjustable instanceof SaleInterface) {
                $parentResult = $this->calculateSale($adjustable, true);
            } elseif ($adjustable instanceof SaleItemInterface) {
                $parentResult = $this->calculateSaleItem($adjustable, true);
            } else {
                throw new InvalidArgumentException('Unexpected adjustable.');
            }
        }

        $result = new Result();

        $mode = $adjustment->getMode();
        if (AdjustmentModes::MODE_PERCENT === $mode) {
            $adjustmentRate = $adjustment->getAmount() / 100;

            // Apply discount rate to base
            $result->addBase(-$this->round($parentResult->getBase() * $adjustmentRate));

            // Apply discount rate to taxes
            foreach ($parentResult->getTaxes() as $tax) {
                $taxBase = -$this->round($tax->getBase() * $adjustmentRate);
                $result->addTax($tax->getName(), $tax->getRate(), $taxBase);
            }
        } elseif (AdjustmentModes::MODE_FLAT === $mode) {
            // Apply discount amount to base
            $result->addBase(-$this->round($adjustment->getAmount()));

            // Dispatch the discount amount over taxes
            foreach ($parentResult->getTaxes() as $tax) {
                $taxBase = -$this->round($adjustment->getAmount() * $tax->getBase() / $parentResult->getBase());
                $result->addTax($tax->getName(), $tax->getRate(), $taxBase);
            }
        } else {
            throw new InvalidArgumentException("Unexpected adjustment mode '$mode'.");
        }

        return $result;
    }

    /**
     * Calculate the taxation adjustments.
     *
     * @param Result              $result
     * @param AdjustableInterface $adjustable
     * @param float               $base
     */
    protected function addTaxation(Result $result, AdjustableInterface $adjustable, $base)
    {
        if ($adjustable->hasAdjustments(AdjustmentTypes::TYPE_TAXATION)) {
            $adjustments = $adjustable->getAdjustments(AdjustmentTypes::TYPE_TAXATION);
            foreach ($adjustments as $adjustment) {
                // Only 'percent' mode adjustments are allowed here.
                $this->assertAdjustmentMode($adjustment, AdjustmentModes::MODE_PERCENT);

                $result->addTax($adjustment->getDesignation(), $adjustment->getAmount(), $base);
            }
        }
    }

    /**
     * Asserts that the adjustment type is as expected.
     *
     * @param AdjustmentInterface $adjustment
     * @param string              $expectedType
     */
    protected function assertAdjustmentType(AdjustmentInterface $adjustment, $expectedType)
    {
        if ($expectedType !== $type = $adjustment->getType()) {
            throw new InvalidArgumentException("Unexpected adjustment type '$type'.");
        }
    }

    /**
     * Asserts that the adjustment mode is as expected.
     *
     * @param AdjustmentInterface $adjustment
     * @param string              $expectedMode
     */
    protected function assertAdjustmentMode(AdjustmentInterface $adjustment, $expectedMode)
    {
        if ($expectedMode !== $mode = $adjustment->getMode()) {
            throw new InvalidArgumentException("Unexpected adjustment mode '$mode'.");
        }
    }

    /**
     * Rounds the results.
     *
     * @param float $result
     *
     * @return float
     */
    private function round($result)
    {
        // TODO precision based on currency
        return round($result, 2);
    }
}

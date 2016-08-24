<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class Calculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Calculator implements CalculatorInterface
{
    /**
     * @var ResultCache
     */
    private $cache;

    /**
     * @var string
     */
    private $mode = self::MODE_NET;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cache = new ResultCache();
    }

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
    public function calculateSaleItem(SaleItemInterface $item)
    {
        // TODO don't calculate twice

        $result = new Result();

        if ($item->hasChildren()) { // Calculate as a "parent" item

            // Merge children results
            foreach ($item->getChildren() as $child) {
                $result->merge($this->calculateSaleItem($child));
            }

        } else { // Calculate as a "child" item

            // Item total quantity
            $quantity = $item->getQuantity();
            $parent = $item;
            while (null !== $parent = $parent->getParent()) {
                $quantity *= $parent->getQuantity();
            }

            switch ($this->mode) {
                case self::MODE_NET :
                    $base = $this->round($item->getNetPrice()) * $quantity;

                    $result->addBase($base);

                    // Taxes
                    if ($item->hasAdjustments(AdjustmentTypes::TYPE_TAXATION)) {
                        $adjustments = $item->getAdjustments(AdjustmentTypes::TYPE_TAXATION);
                        foreach ($adjustments as $adjustment) {
                            $this->assertAdjustmentMode($adjustment, AdjustmentModes::MODE_PERCENT);

                            $result->addTax(
                                $adjustment->getDesignation(),
                                $adjustment->getAmount(),
                                $this->round($base * $adjustment->getAmount() / 100)
                            );
                        }
                    }
                    break;

                case self::MODE_GROSS :
                    $base = $item->getNetPrice() * $quantity;
                    $roundedBase = $this->round($base);

                    $result->addBase($roundedBase);

                    // Taxes
                    if ($item->hasAdjustments(AdjustmentTypes::TYPE_TAXATION)) {
                        $adjustments = $item->getAdjustments(AdjustmentTypes::TYPE_TAXATION);
                        foreach ($adjustments as $adjustment) {
                            $this->assertAdjustmentMode($adjustment, AdjustmentModes::MODE_PERCENT);

                            $gross = $this->round($base * (1 + $adjustment->getAmount() / 100));
                            $amount = $gross - $roundedBase;

                            $result->addTax(
                                $adjustment->getDesignation(),
                                $adjustment->getAmount(),
                                $amount
                            );
                        }
                    }
                    break;

                default:
                    throw new InvalidArgumentException('Unexpected mode.');
            }
        }

        // Discount adjustments
        if ($item->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            $parentResult = clone $result;
            $adjustments = $item->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT);
            foreach ($adjustments as $adjustment) {
                // Only 'percent' mode adjustments are allowed here.
                $this->assertAdjustmentMode($adjustment, AdjustmentModes::MODE_PERCENT);

                $result->merge($this->calculateDiscountAdjustment($adjustment, $parentResult));
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateSale(SaleInterface $sale)
    {
        // TODO don't calculate twice

        $result = new Result();

        // Items result
        if ($sale->hasItems()) {
            foreach ($sale->getItems() as $item) {
                $result->merge($this->calculateSaleItem($item));
            }
        }

        // Discount adjustments results
        if ($sale->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            $parentResult = clone $result;
            $adjustments = $sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT);
            foreach ($adjustments as $adjustment) {
                $result->merge($this->calculateDiscountAdjustment($adjustment, $parentResult));
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateDiscountAdjustment(AdjustmentInterface $adjustment, Result $parentResult)
    {
        $this->assertAdjustmentType($adjustment, AdjustmentTypes::TYPE_DISCOUNT);

        // TODO don't calculate twice

        $result = new Result();

        $mode = $adjustment->getMode();
        if (AdjustmentModes::MODE_PERCENT === $mode) {
            $adjustmentRate = $adjustment->getAmount() / 100;

            $result->addBase(-$this->round($parentResult->getBase() * $adjustmentRate));

            foreach ($parentResult->getTaxes() as $taxAmount) {
                $result->addTax(
                    $taxAmount->getName(),
                    $taxAmount->getRate(),
                    -$this->round($taxAmount->getAmount() * $adjustmentRate)
                );
            }
        } elseif (AdjustmentModes::MODE_FLAT === $mode) {
            $result->addBase(- $this->round($adjustment->getAmount()));
        } else {
            throw new InvalidArgumentException("Unexpected adjustment mode '$mode'.");
        }

        return $result;
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

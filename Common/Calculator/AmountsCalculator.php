<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

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
    public function calculateSale(SaleInterface $sale, $gross = false)
    {
        // TODO don't calculate twice
        // TODO enable result caching

        $result = new Result();

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

        // TODO disable result caching

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleItem(SaleItemInterface $item, $gross = false)
    {
        // TODO don't calculate twice

        $result = new Result();

        if ($item->hasChildren()) { // Calculate as a "parent" item

            // Merge children results
            foreach ($item->getChildren() as $child) {
                $result->merge($this->calculateSaleItem($child)); // not gross
            }

        } else { // Calculate as a "child" item

            switch ($this->mode) {
                case self::MODE_NET :
                    $base = $this->round($item->getNetPrice()) * $item->getTotalQuantity();

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
                    $base = $item->getNetPrice() * $item->getTotalQuantity();
                    $roundedBase = $this->round($base);

                    $result->addBase($roundedBase);

                    // Taxes
                    if ($item->hasAdjustments(AdjustmentTypes::TYPE_TAXATION)) {
                        $adjustments = $item->getAdjustments(AdjustmentTypes::TYPE_TAXATION);
                        foreach ($adjustments as $adjustment) {
                            $this->assertAdjustmentMode($adjustment, AdjustmentModes::MODE_PERCENT);

                            $result->addTax(
                                $adjustment->getDesignation(),
                                $adjustment->getAmount(),
                                $this->round($base * (1 + $adjustment->getAmount() / 100)) - $roundedBase
                            );
                        }
                    }
                    break;

                default:
                    throw new InvalidArgumentException('Unexpected mode.');
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
    public function calculateDiscountAdjustment(AdjustmentInterface $adjustment)
    {
        $this->assertAdjustmentType($adjustment, AdjustmentTypes::TYPE_DISCOUNT);

        // TODO don't calculate twice

        $adjustable = $adjustment->getAdjustable();
        if ($adjustable instanceof SaleInterface) {
            $parentResult = $this->calculateSale($adjustable, true);
        } elseif ($adjustable instanceof SaleItemInterface) {
            $parentResult = $this->calculateSaleItem($adjustable, true);
        } else {
            throw new InvalidArgumentException('Unexpected adjustable.');
        }

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

            // TODO calculate per tax discount (dispatch regarding to each tax total)

            /*$rate = $adjustment->getAmount() / $parentResult->getBase();
            foreach ($parentResult->getTaxes() as $taxAmount) {
                $result->addTax(
                    $taxAmount->getName(),
                    $taxAmount->getRate(),
                    -$this->round($taxAmount->getAmount() * $rate)
                );
            }*/

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

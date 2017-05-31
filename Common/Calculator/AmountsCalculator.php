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
                $result->merge($this->calculateSaleItem($item)->multiply($item->getQuantity()));
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

        // TODO disable result caching

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateShipment(SaleInterface $sale)
    {
        // TODO don't calculate twice

        $result = new Result();

        if (0 < $sale->getShipmentAmount()) {
            // TODO round base regarding to calculator mode ?
            $result
                ->addBase($base = $sale->getShipmentAmount())
                ->merge($this->calculateTaxationAdjustments($sale, $base));
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleItem(SaleItemInterface $item, $gross = false, $single = false)
    {
        // TODO don't calculate twice

        $result = new Result();

        if (!$item->isCompound()) {
            $base = $this->mode === self::MODE_NET
                ? $this->round($item->getNetPrice())
                : $item->getNetPrice();

            $result
                ->addBase($base)
                ->merge($this->calculateTaxationAdjustments($item, $base));
        }

        /*switch ($this->mode) {
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
        }*/

        if ($item->hasChildren() && !$single) { // Calculate as a "parent" item
            // Merge children results
            foreach ($item->getChildren() as $child) {
                // Item result must take account of child's discounts, so not gross
                $result->merge($this->calculateSaleItem($child)->multiply($child->getQuantity()));
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

        // TODO don't calculate twice

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

            $result->addBase(-$this->round($parentResult->getBase() * $adjustmentRate));

            foreach ($parentResult->getTaxes() as $tax) {
                $result->addTax(
                    $tax->getName(),
                    $tax->getRate(),
                    -$this->round($tax->getAmount() * $adjustmentRate)
                );
            }
        } elseif (AdjustmentModes::MODE_FLAT === $mode) {
            $result->addBase(-$this->round($adjustment->getAmount()));

            // TODO calculate per tax discount (dispatch regarding to each tax total)

            /*$rate = $adjustment->getAmount() / $parentResult->getBase();
            foreach ($parentResult->getTaxes() as $tax) {
                $result->addTax(
                    $tax->getName(),
                    $tax->getRate(),
                    -$this->round($tax->getAmount() * $rate)
                );
            }*/

        } else {
            throw new InvalidArgumentException("Unexpected adjustment mode '$mode'.");
        }

        return $result;
    }

    /**
     * Calculate the taxation adjustments.
     *
     * @param AdjustableInterface $adjustable
     * @param float               $base
     *
     * @return Result
     */
    protected function calculateTaxationAdjustments(AdjustableInterface $adjustable, $base)
    {
        $result = new Result();

        if (0 == $base) {
            return $result;
        }

        if ($adjustable->hasAdjustments(AdjustmentTypes::TYPE_TAXATION)) {
            $adjustments = $adjustable->getAdjustments(AdjustmentTypes::TYPE_TAXATION);
            foreach ($adjustments as $adjustment) {
                // Only 'percent' mode adjustments are allowed here.
                $this->assertAdjustmentMode($adjustment, AdjustmentModes::MODE_PERCENT);

                if ($this->mode === self::MODE_NET) {
                    // By multiplication
                    $amount = $this->round($base * $adjustment->getAmount() / 100);
                } else {
                    // By difference (ATI - NET)
                    $amount = $this->round($base * (1 + $adjustment->getAmount() / 100)) - $this->round($base);
                }

                $result->addTax($adjustment->getDesignation(), $adjustment->getAmount(), $amount);
            }
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

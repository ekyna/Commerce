<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class Builder
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Builder
{
    /**
     * @var CalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param CalculatorInterface $calculator
     */
    public function __construct(CalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Builds the sale view.
     *
     * @param SaleInterface $sale
     *
     * @return Sale
     */
    public function buildSaleView(SaleInterface $sale)
    {
        $grossResult = $this->calculator->calculateSale($sale, true);
        $finalResult = $this->calculator->calculateSale($sale);

        $grossTotal = new Total(
            $grossResult->getBase(),
            $grossResult->getTaxesTotal(),
            $grossResult->getTotal()
        );

        $finalTotal = new Total(
            $finalResult->getBase(),
            $finalResult->getTaxesTotal(),
            $finalResult->getTotal()
        );

        return new Sale(
            CalculatorInterface::MODE_NET, // TODO This should be resolved regarding to the customer group
            $grossTotal,
            $finalTotal,
            $this->buildSaleItemsLinesViews($sale),
            $this->buildSaleDiscountsLinesViews($sale),
            $this->buildSaleTaxesViews($sale)
        );
    }

    /**
     * Builds the sale taxes views.
     *
     * @param SaleInterface $sale
     *
     * @return Tax[]
     */
    protected function buildSaleTaxesViews(SaleInterface $sale)
    {
        // TODO unnecessary recalculation (see calculator "don't build amounts twice")
        $amounts = $this->calculator->calculateSale($sale);

        $taxes = [];
        foreach ($amounts->getTaxes() as $tax) {
            $taxes[] = new Tax($tax->getName(), $tax->getAmount());
        }

        return $taxes;
    }

    /**
     * Builds the sale lines views.
     *
     * @param SaleInterface $sale
     *
     * @return Line[]
     */
    protected function buildSaleItemsLinesViews(SaleInterface $sale)
    {
        $lines = [];

        foreach ($sale->getItems() as $item) {
            $lines[] = $this->buildSaleItemLineView($item);
        }

        return $lines;
    }

    /**
     * Builds the sale discounts lines views.
     *
     * @param SaleInterface $sale
     *
     * @return Line[]
     */
    protected function buildSaleDiscountsLinesViews(SaleInterface $sale)
    {
        $lines = [];

        if ($sale->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $lines[] = $this->buildDiscountAdjustmentLine($adjustment);
            }
        }

        return $lines;
    }

    /**
     * Builds the sale line view.
     *
     * @param SaleItemInterface $item
     *
     * @return Line
     */
    protected function buildSaleItemLineView(SaleItemInterface $item)
    {
        $gross = !$item->hasChildren() && $item->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT);

        $amounts = $this->calculator->calculateSaleItem($item, $gross);

        $lines = [];
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $lines[] = $this->buildSaleItemLineView($child);
            }
        }
        if ($item->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($item->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $lines[] = $this->buildDiscountAdjustmentLine($adjustment);
            }
        }

        // Item total quantity
        $quantity = $item->getQuantity();
        $parent = $item;
        while (null !== $parent = $parent->getParent()) {
            $quantity *= $parent->getQuantity();
        }

        $taxSum = 0;
        foreach ($amounts->getTaxes() as $tax) {
            $taxSum += $tax->getAmount();
        }

        return new Line(
            $item->getDesignation(),
            $item->getReference(),
            $item->getNetPrice(),
            $quantity,
            $amounts->getBase(),
            $taxSum,
            $amounts->getTotal(),
            $lines,
            $item->hasChildren()
        );
    }

    /**
     * Builds the discount adjustment line.
     *
     * @param AdjustmentInterface $adjustment
     *
     * @return Line
     */
    protected function buildDiscountAdjustmentLine(AdjustmentInterface $adjustment)
    {
        if (AdjustmentTypes::TYPE_DISCOUNT !== $adjustment->getType()) {
            throw new InvalidArgumentException("Unexpected adjustment type.");
        }

        $amounts = $this->calculator->calculateDiscountAdjustment($adjustment);

        $taxSum = 0;
        foreach ($amounts->getTaxes() as $tax) {
            $taxSum += $tax->getAmount();
        }

        return new Line(
            $adjustment->getDesignation(),
            '',
            null,
            1,
            $amounts->getBase(),
            $taxSum,
            $amounts->getTotal()
        );
    }
}

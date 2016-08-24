<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Calculator\Result;
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
        $amounts = $this->calculator->calculateSale($sale);

        return new Sale(
            CalculatorInterface::MODE_NET, // TODO This should be resolved regarding to the customer group
            $this->buildSaleLinesViews($sale),
            $amounts->getBase(),
            $this->buildSaleLinesViews($sale),
            $amounts->getTotal()
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
    protected function buildSaleLinesViews(SaleInterface $sale)
    {
        // TODO unnecessary recalculation (see calculator "don't build amounts twice")
        $amounts = $this->calculator->calculateSale($sale);

        $lines = [];

        foreach ($sale->getItems() as $item) {
            $lines[] = $this->buildSaleLineView($item);
        }

        if ($sale->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $lines[] = $this->buildDiscountAdjustmentLine($adjustment, $amounts);
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
    protected function buildSaleLineView(SaleItemInterface $item)
    {
        $amounts = $this->calculator->calculateSaleItem($item);

        $lines = [];
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $lines[] = $this->buildSaleLineView($child);
            }
        }
        if ($item->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($item->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $lines[] = $this->buildDiscountAdjustmentLine($adjustment, $amounts);
            }
        }

        $tax = 0;
        foreach ($amounts->getTaxes() as $tax) {
            $tax += $tax->getAmount();
        }

        return new Line(
            $item->getDesignation(),
            $item->getReference(),
            $item->getNetPrice(),
            $item->getQuantity(),
            $amounts->getBase(),
            $tax,
            $amounts->getTotal(),
            $lines
        );
    }

    /**
     * Builds the discount adjustment line.
     *
     * @param AdjustmentInterface $adjustment
     * @param Result              $parentAmounts
     *
     * @return Line
     */
    protected function buildDiscountAdjustmentLine(AdjustmentInterface $adjustment, Result $parentAmounts)
    {
        if (AdjustmentTypes::TYPE_DISCOUNT !== $adjustment->getType()) {
            throw new InvalidArgumentException("Unexpected adjustment type.");
        }

        $amounts = $this->calculator->calculateDiscountAdjustment($adjustment, $parentAmounts);

        $tax = 0;
        foreach ($amounts->getTaxes() as $tax) {
            $tax += $tax->getAmount();
        }

        return new Line(
            $adjustment->getDesignation(),
            '',
            $amounts->getBase(),
            1,
            $amounts->getBase(),
            $tax,
            $amounts->getTotal()
        );
    }
}

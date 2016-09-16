<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ViewBuilder
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ViewBuilder
{
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var array
     */
    private $options;

    /**
     * @var CalculatorInterface
     */
    private $calculator;

    /**
     * @var int
     */
    private $lineNumber;


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
     * @param array         $options
     *
     * @return SaleView
     */
    public function buildSaleView(SaleInterface $sale, array $options = [])
    {
        $this->options = $this->getOptionsResolver()->resolve($options);

        $this->lineNumber = 1;

        $grossResult = $this->calculator->calculateSale($sale, true);
        $finalResult = $this->calculator->calculateSale($sale);

        $grossTotal = new TotalView(
            $grossResult->getBase(),
            $grossResult->getTaxTotal(),
            $grossResult->getTotal()
        );

        $finalTotal = new TotalView(
            $finalResult->getBase(),
            $finalResult->getTaxTotal(),
            $finalResult->getTotal()
        );

        $view = new SaleView(
            CalculatorInterface::MODE_NET, // TODO This should be resolved regarding to the customer group
            $grossTotal,
            $finalTotal,
            $this->buildSaleItemsLinesViews($sale),
            $this->buildSaleDiscountsLinesViews($sale),
            $this->buildSaleTaxesViews($sale)
        );

        $this->buildViewVars($view, 'sale_vars', [$sale]);

        return $view;
    }

    /**
     * Builds the sale taxes views.
     *
     * @param SaleInterface $sale
     *
     * @return TaxView[]
     */
    private function buildSaleTaxesViews(SaleInterface $sale)
    {
        // TODO unnecessary recalculation (see calculator "don't build amounts twice")
        $amounts = $this->calculator->calculateSale($sale);

        $taxes = [];
        foreach ($amounts->getTaxes() as $tax) {
            $taxes[] = new TaxView($tax->getName(), $tax->getAmount());
        }

        return $taxes;
    }

    /**
     * Builds the sale lines views.
     *
     * @param SaleInterface $sale
     *
     * @return LineView[]
     */
    private function buildSaleItemsLinesViews(SaleInterface $sale)
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
     * @return LineView[]
     */
    private function buildSaleDiscountsLinesViews(SaleInterface $sale)
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
     * @param int               $level
     *
     * @return LineView
     */
    private function buildSaleItemLineView(SaleItemInterface $item, $level = 0)
    {
        $gross = !$item->hasChildren() && $item->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT);

        $lineNumber = $this->lineNumber++;
        $amounts = $this->calculator->calculateSaleItem($item, $gross);

        $lines = [];
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $lines[] = $this->buildSaleItemLineView($child, $level + 1);
            }
        }
        if ($item->hasAdjustments(AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($item->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $lines[] = $this->buildDiscountAdjustmentLine($adjustment, $level + 1);
            }
        }

        // Item total quantity
        $quantity = $item->getQuantity();
        $parent = $item;
        while (null !== $parent = $parent->getParent()) {
            $quantity *= $parent->getQuantity();
        }

        $view = new LineView(
            $item->getId(),
            $lineNumber,
            $level,
            $item->getDesignation(),
            $item->getReference(),
            $item->getNetPrice(),
            $quantity,
            $amounts->getBase(),
            $amounts->getTaxRate(),
            $amounts->getTaxTotal(),
            $amounts->getTotal(),
            $lines,
            $item->isImmutable()
        );

        $this->buildViewVars($view, 'item_vars', [$item]);

        return $view;
    }

    /**
     * Builds the discount adjustment line view.
     *
     * @param AdjustmentInterface $adjustment
     * @param int                 $level
     *
     * @return LineView
     */
    private function buildDiscountAdjustmentLine(AdjustmentInterface $adjustment, $level = 0)
    {
        if (AdjustmentTypes::TYPE_DISCOUNT !== $adjustment->getType()) {
            throw new InvalidArgumentException("Unexpected adjustment type.");
        }

        $lineNumber = $this->lineNumber++;
        $amounts = $this->calculator->calculateDiscountAdjustment($adjustment);

        $view = new LineView(
            $adjustment->getId(),
            $lineNumber,
            $level,
            $adjustment->getDesignation(),
            '',
            null,
            1,
            $amounts->getBase(),
            null,
            $amounts->getTaxTotal(),
            $amounts->getTotal()
            // lines
            // immutable
        );

        $this->buildViewVars($view, 'adjustment_vars', [$adjustment]);

        return $view;
    }

    /**
     * Builds the view vars.
     *
     * @param AbstractView $view
     * @param              $optionsKey
     * @param array        $callableArgs
     */
    private function buildViewVars(AbstractView $view, $optionsKey, array $callableArgs)
    {
        $saleVars = $this->options[$optionsKey];

        if (is_callable($saleVars)) {
            $view->vars = call_user_func_array($saleVars, $callableArgs);
        } elseif (is_array($saleVars)) {
            $view->vars = $saleVars;
        }
    }

    /**
     * Returns the options resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        if (null !== $this->optionsResolver) {
            return $this->optionsResolver;
        }

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'sale_vars'       => null,
                'item_vars'       => null,
                'adjustment_vars' => null,
            ])
            ->setAllowedTypes('sale_vars', ['null', 'array', 'callable'])
            ->setAllowedTypes('item_vars', ['null', 'array', 'callable'])
            ->setAllowedTypes('adjustment_vars', ['null', 'array', 'callable']);

        // TODO validate callable's args types

        return $this->optionsResolver = $resolver;
    }
}

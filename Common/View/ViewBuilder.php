<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ViewBuilder
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ViewBuilder
{
    /**
     * @var ViewTypeRegistryInterface
     */
    private $registry;

    /**
     * @var AmountsCalculatorInterface
     */
    private $calculator;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ViewTypeInterface[]
     */
    private $types;

    /**
     * @var int
     */
    private $lineNumber;


    /**
     * Constructor.
     *
     * @param ViewTypeRegistryInterface  $registry
     * @param AmountsCalculatorInterface $calculator
     */
    public function __construct(
        ViewTypeRegistryInterface $registry,
        AmountsCalculatorInterface $calculator
    ) {
        $this->registry = $registry;
        $this->calculator = $calculator;
    }

    /**
     * Builds the sale view.
     *
     * @param Model\SaleInterface $sale
     * @param array               $options
     *
     * @return SaleView
     */
    public function buildSaleView(Model\SaleInterface $sale, array $options = [])
    {
        $this->options = $this->getOptionsResolver()->resolve($options);

        $this->types = $this->registry->getTypesForSale($sale);
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
            AmountsCalculatorInterface::MODE_NET, // TODO This should be resolved regarding to the customer group
            $grossTotal,
            $finalTotal,
            $this->buildSaleItemsLinesViews($sale),
            $this->buildSaleDiscountsLinesViews($sale),
            $this->buildShipmentLine($sale),
            $this->buildSaleTaxesViews($sale)
        );

        foreach ($this->types as $type) {
            $type->buildSaleView($sale, $view, $this->options);
        }

        return $view;
    }

    /**
     * Builds the sale taxes views.
     *
     * @param Model\SaleInterface $sale
     *
     * @return TaxView[]
     */
    private function buildSaleTaxesViews(Model\SaleInterface $sale)
    {
        // TODO unnecessary recalculation (see calculator "don't build amounts twice")
        $amounts = $this->calculator->calculateSale($sale);

        $taxes = [];
        foreach ($amounts->getTaxes() as $tax) {
            $taxes[] = new TaxView($tax->getName(), $tax->getAmount());
        }

        /*foreach ($this->types as $type) {
            $type->buildSaleTaxesView($sale, $view, $options);
        }*/

        return $taxes;
    }

    /**
     * Builds the sale lines views.
     *
     * @param Model\SaleInterface $sale
     *
     * @return LineView[]
     */
    private function buildSaleItemsLinesViews(Model\SaleInterface $sale)
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
     * @param Model\SaleInterface $sale
     *
     * @return LineView[]
     */
    private function buildSaleDiscountsLinesViews(Model\SaleInterface $sale)
    {
        $lines = [];

        if ($sale->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $lines[] = $this->buildDiscountAdjustmentLine($adjustment);
            }
        }

        return $lines;
    }

    /**
     * Builds the sale line view.
     *
     * @param Model\SaleItemInterface $item
     * @param int                     $level
     *
     * @return LineView
     */
    private function buildSaleItemLineView(Model\SaleItemInterface $item, $level = 0)
    {
        $gross = !$item->hasChildren() && $item->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT);

        $lineNumber = $this->lineNumber++;
        $amounts = $this->calculator->calculateSaleItem($item, $gross)->multiply($item->getQuantity());

        $lines = [];
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $lines[] = $this->buildSaleItemLineView($child, $level + 1);
            }
        }
        if ($item->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($item->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
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
            'item_' . ($lineNumber-1),
            'item_' . $item->getId(),
            $lineNumber,
            $level,
            $item->getDesignation(),
            $item->getReference(),
            $item->getNetPrice(),
            $quantity,
            $amounts->getBase(),
            $amounts->getTaxRates(),
            $amounts->getTaxTotal(),
            $amounts->getTotal(),
            $lines,
            $item->hasChildren()
        );

        foreach ($this->types as $type) {
            $type->buildItemView($item, $view, $this->options);
        }

        return $view;
    }

    /**
     * Builds the discount adjustment line view.
     *
     * @param Model\AdjustmentInterface $adjustment
     * @param int                       $level
     *
     * @return LineView
     */
    private function buildDiscountAdjustmentLine(Model\AdjustmentInterface $adjustment, $level = 0)
    {
        if (Model\AdjustmentTypes::TYPE_DISCOUNT !== $adjustment->getType()) {
            throw new InvalidArgumentException("Unexpected adjustment type.");
        }

        $lineNumber = $this->lineNumber++;
        $amounts = $this->calculator->calculateDiscountAdjustment($adjustment);

        $view = new LineView(
            'adjustment_' . ($lineNumber-1),
            'adjustment_' . $adjustment->getId(),
            $lineNumber,
            $level,
            $adjustment->getDesignation(),
            '',
            null,
            null,
            $amounts->getBase(),
            [],
            $amounts->getTaxTotal(),
            $amounts->getTotal()
            // lines
            // node
        );

        foreach ($this->types as $type) {
            $type->buildAdjustmentView($adjustment, $view, $this->options);
        }

        return $view;
    }

    /**
     * Builds the shipment adjustment line view.
     *
     * @param Model\SaleInterface $sale
     *
     * @return LineView|null
     */
    private function buildShipmentLine(Model\SaleInterface $sale)
    {
        if (!$sale->requiresShipment() && 0 == $sale->getShipmentAmount()) {
            return null;
        }

        $amounts = $this->calculator->calculateShipment($sale);

        $lineNumber = $this->lineNumber++;

        // Method title
        $designation = 'Frais de port'; // TODO translate ?
        if (null !== $method = $sale->getPreferredShipmentMethod()) {
            $designation = $method->getTitle();
        }
        // Total weight
        $designation .= ' (' . number_format($sale->getWeightTotal(), 3, ',', '') . ' kg)';

        $view = new LineView(
            'shipment',
            'shipment',
            $lineNumber,
            0,
            $designation,
            '',
            null,
            1,
            $amounts->getBase(),
            $amounts->getTaxRates(),
            $amounts->getTaxTotal(),
            $amounts->getTotal()
            // lines
            // node
        );

        foreach ($this->types as $type) {
            $type->buildShipmentView($sale, $view, $this->options);
        }

        return $view;
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
                'private'  => false,
                'editable' => false,
                'template' => function (Options $options) {
                    if (true === $options['editable']) {
                        return 'EkynaCommerceBundle:Common:sale_view_editable.html.twig';
                    }

                    return 'EkynaCommerceBundle:Common:sale_view.html.twig';
                },
            ])
            ->setAllowedTypes('private', 'bool')
            ->setAllowedTypes('editable', 'bool')
            ->setAllowedTypes('template', ['null', 'string']);

        return $this->optionsResolver = $resolver;
    }
}

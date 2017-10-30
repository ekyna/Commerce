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
     * @var string
     */
    private $defaultTemplate;

    /**
     * @var string
     */
    private $editableTemplate;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var array
     */
    private $options;

    /**
     * @var SaleView
     */
    private $view;

    /**
     * @var ViewTypeInterface[]
     */
    private $types;

    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var Formatter
     */
    private $formatter;


    /**
     * Constructor.
     *
     * @param ViewTypeRegistryInterface  $registry
     * @param AmountsCalculatorInterface $calculator
     * @param string $defaultTemplate
     * @param string $editableTemplate
     */
    public function __construct(
        ViewTypeRegistryInterface $registry,
        AmountsCalculatorInterface $calculator,
        $defaultTemplate = '@Commerce/Sale/view.html.twig',
        $editableTemplate = '@Commerce/Sale/view_editable.html.twig'
    ) {
        $this->registry = $registry;
        $this->calculator = $calculator;
        $this->defaultTemplate = $defaultTemplate;
        $this->editableTemplate = $editableTemplate;
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
        $this->initialize($sale, $options);

        // TODO Mode should be resolved regarding to the customer group
        $this->view = new SaleView(
            AmountsCalculatorInterface::MODE_NET,
            $this->options['template']
        );

        // Gross total view
        $grossResult = $this->calculator->calculateSale($sale, true);
        $this->view->setGross(new TotalView(
            $this->formatter->currency($grossResult->getBase()),
            $this->formatter->currency($grossResult->getTaxTotal()),
            $this->formatter->currency($grossResult->getTotal())
        ));

        // Final total view
        $finalResult = $this->calculator->calculateSale($sale);
        $this->view->setFinal(new TotalView(
            $this->formatter->currency($finalResult->getBase()),
            $this->formatter->currency($finalResult->getTaxTotal()),
            $this->formatter->currency($finalResult->getTotal())
        ));

        // Items lines
        $this->buildSaleItemsLinesViews($sale);
        // Discounts lines
        $this->buildSaleDiscountsLinesViews($sale);
        // Shipment line
        $this->buildShipmentLine($sale);
        // Taxes views
        $this->buildSaleTaxesViews($sale);

        // Extends
        foreach ($this->types as $type) {
            $type->buildSaleView($sale, $this->view, $this->options);
        }

        return $this->view;
    }

    /**
     * Initializes the view builder.
     *
     * @param Model\SaleInterface $sale
     * @param array               $options
     */
    private function initialize(Model\SaleInterface $sale, array $options = [])
    {
        $this->options = $this->getOptionsResolver()->resolve($options);
        $this->lineNumber = 1;

        $currency = $sale->getCurrency()->getCode();
        $locale = $this->options['locale'];

        if (!(
            null !== $this->formatter &&
            $this->formatter->getLocale() === $locale &&
            $this->formatter->getCurrency() !== $currency
        )) {
            $this->formatter = new Formatter($locale, $currency);
        }

        $this->types = $this->registry->getTypesForSale($sale);

        foreach ($this->types as $type) {
            $type->setFormatter($this->formatter);
        }
    }

    /**
     * Builds the sale taxes views.
     *
     * @param Model\SaleInterface $sale
     */
    private function buildSaleTaxesViews(Model\SaleInterface $sale)
    {
        if (!$this->options['taxes_view']) {
            return;
        }

        $amounts = $this->calculator->calculateSale($sale);

        foreach ($amounts->getTaxes() as $tax) {
            $this->view->addTax(new TaxView(
                $tax->getName(),
                $this->formatter->currency($tax->getAmount())
            ));
        }
    }

    /**
     * Builds the sale lines views.
     *
     * @param Model\SaleInterface $sale
     */
    private function buildSaleItemsLinesViews(Model\SaleInterface $sale)
    {
        if (!$sale->hasItems()) {
            return;
        }

        foreach ($sale->getItems() as $item) {
            $this->view->addItem($this->buildSaleItemLineView($item));
        }
    }

    /**
     * Builds the sale discounts lines views.
     *
     * @param Model\SaleInterface $sale
     */
    private function buildSaleDiscountsLinesViews(Model\SaleInterface $sale)
    {
        if (!$sale->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
            return;
        }

        foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
            $this->view->addDiscount($this->buildDiscountAdjustmentLine($adjustment));
        }
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
        $lineNumber = $this->lineNumber++;

        $view = new LineView(
            'item_' . ($lineNumber - 1),
            'item_' . $item->getId(),
            $lineNumber,
            $level
        );

        $gross = !$item->isCompound() && $item->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT);

        $amounts = $this
            ->calculator
            ->calculateSaleItem($item, $gross, true);

        $view
            ->setDesignation($item->getDesignation())
            ->setDescription($item->getDescription())
            ->setReference($item->getReference())
            ->setUnit($this->formatter->currency($item->getNetPrice()))
            ->setQuantity($item->getTotalQuantity())
            ->setBase($this->formatter->currency($amounts->getBase()))
            ->setTaxRates($this->formatter->taxRates($amounts->getTaxRates()))
            ->setTaxAmount($this->formatter->currency($amounts->getTaxTotal()))
            ->setTotal($this->formatter->currency($amounts->getTotal()))
            ->setNode($item->isCompound());

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $view->addLine($this->buildSaleItemLineView($child, $level + 1));
            }
        }

        if ($item->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($item->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $view->addLine($this->buildDiscountAdjustmentLine($adjustment, $level + 1));
            }
        }

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

        $view = new LineView(
            'adjustment_' . ($lineNumber - 1),
            'adjustment_' . $adjustment->getId(),
            $lineNumber,
            $level
        );

        if (empty($designation = $adjustment->getDesignation())) {
            $designation = 'Discount ';
            if ($adjustment->getMode() === Model\AdjustmentModes::MODE_PERCENT) {
                $designation .= $this->formatter->percent($adjustment->getAmount());
            }
        }

        $amounts = $this->calculator->calculateDiscountAdjustment($adjustment);

        $view
            ->setDesignation($designation)
            ->setBase($this->formatter->currency($amounts->getBase()))
            ->setTaxAmount($this->formatter->currency($amounts->getTaxTotal()))
            ->setTotal($this->formatter->currency($amounts->getTotal()));

        foreach ($this->types as $type) {
            $type->buildAdjustmentView($adjustment, $view, $this->options);
        }

        return $view;
    }

    /**
     * Builds the shipment adjustment line view.
     *
     * @param Model\SaleInterface $sale
     */
    private function buildShipmentLine(Model\SaleInterface $sale)
    {
        if (!$sale->requiresShipment() && 0 == $sale->getShipmentAmount()) {
            return;
        }

        $amounts = $this->calculator->calculateShipment($sale);

        $lineNumber = $this->lineNumber++;

        $view = new LineView(
            'shipment',
            'shipment',
            $lineNumber,
            0
        );

        // Method title
        $designation = 'Shipping cost';
        if (null !== $method = $sale->getPreferredShipmentMethod()) {
            $designation = $method->getTitle();
        }

        // Total weight
        $designation .= ' (' . $this->formatter->number($sale->getWeightTotal()) . ' kg)';

        $view
            ->setDesignation($designation)
            ->setBase($this->formatter->currency($amounts->getBase()))
            ->setTaxRates($this->formatter->taxRates($amounts->getTaxRates()))
            ->setTaxAmount($this->formatter->currency($amounts->getTaxTotal()))
            ->setTotal($this->formatter->currency($amounts->getTotal()));

        foreach ($this->types as $type) {
            $type->buildShipmentView($sale, $view, $this->options);
        }

        $this->view->setShipment($view);
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
                'private'    => false,
                'editable'   => false,
                'taxes_view' => true,
                'locale'     => \Locale::getDefault(),
                'template'   => function (Options $options) {
                    if (true === $options['editable']) {
                        return $this->editableTemplate;
                    }

                    return $this->defaultTemplate;
                },
            ])
            ->setAllowedTypes('private', 'bool')
            ->setAllowedTypes('editable', 'bool')
            ->setAllowedTypes('taxes_view', 'bool')
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('template', ['null', 'string']);

        return $this->optionsResolver = $resolver;
    }
}

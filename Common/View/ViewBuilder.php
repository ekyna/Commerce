<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
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
     * @var AmountCalculatorInterface
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
     * @param ViewTypeRegistryInterface $registry
     * @param AmountCalculatorInterface $calculator
     * @param string                    $defaultTemplate
     * @param string                    $editableTemplate
     */
    public function __construct(
        ViewTypeRegistryInterface $registry,
        AmountCalculatorInterface $calculator,
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

        $this->view = new SaleView($this->options['template']);

        $this->calculator->calculateSale($sale);

        // Gross total view
        $grossResult = $sale->getGrossResult();
        $this->view->setGross(new TotalView(
            $this->formatter->currency($grossResult->getGross()),
            $this->formatter->currency($grossResult->getDiscount()),
            $this->formatter->currency($grossResult->getBase())
        ));

        // Final total view
        $finalResult = $this->calculator->calculateSale($sale);
        $this->view->setFinal(new TotalView(
            $this->formatter->currency($finalResult->getBase()),
            $this->formatter->currency($finalResult->getTax()),
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

        $columnsCount = 6;
        if ($this->view->vars['line_discounts'] = 0 < count($grossResult->getDiscountAdjustments())) {
            $columnsCount += 3;
        }
        if ($this->view->vars['line_taxes'] = 1 < count($finalResult->getTaxAdjustments())) {
            $columnsCount++;
        }
        if ($this->options['editable']) {
            $columnsCount++;
        }
        $this->view->vars['columns_count'] = $columnsCount;

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

        foreach ($amounts->getTaxAdjustments() as $tax) {
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
            // We don't need to test if null is returned as root items can't be private.
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
            $this->view->addDiscount($this->buildDiscountLine($adjustment));
        }
    }

    /**
     * Builds the sale line view.
     *
     * @param Model\SaleItemInterface $item
     * @param int                     $level
     *
     * @return LineView|null
     */
    private function buildSaleItemLineView(Model\SaleItemInterface $item, $level = 0)
    {
        if (!$this->options['private'] && $item->isPrivate()) {
            return null;
        }

        $lineNumber = $this->lineNumber++;

        $view = new LineView(
            'item_' . ($lineNumber - 1),
            'item_' . $item->getId(),
            $lineNumber,
            $level
        );

        $result = $item->getResult();

        $unit = $gross = $discountRates = $discountAmount = $base = $taxRates = $taxAmount = $total = null;

        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $unit = $this->formatter->currency($result->getUnit());
            $gross = $this->formatter->currency($result->getGross());
            $discountRates = $this->formatter->rates(...$result->getDiscountAdjustments());
            if (0 < $discount = $result->getDiscount()) {
                $discountAmount = $this->formatter->currency($discount);
            }
            $base = $this->formatter->currency($result->getBase());
            $taxRates = $this->formatter->rates(...$result->getTaxAdjustments());
            if (0 < $tax = $result->getTax()) {
                $taxAmount = $this->formatter->currency($tax);
            }
            $total = $this->formatter->currency($result->getTotal());
        }

        // TODO Use packaging format
        if ($item->isPrivate()) {
            $quantity = sprintf(
                '%s (x%s)',
                $this->formatter->number($item->getQuantity()),
                $this->formatter->number($item->getParentsQuantity())
            );
        } else {
            $quantity = $this->formatter->number($item->getTotalQuantity());
        }

        $view
            ->setDesignation($item->getDesignation())
            ->setDescription($item->getDescription())
            ->setReference($item->getReference())
            ->setUnit($unit)
            ->setQuantity($quantity)
            ->setGross($gross)
            ->setDiscountRates($discountRates)
            ->setDiscountAmount($discountAmount)
            ->setBase($base)
            ->setTaxRates($taxRates)
            ->setTaxAmount($taxAmount)
            ->setTotal($total)
            ->setPrivate($item->isPrivate());

        foreach ($this->types as $type) {
            $type->buildItemView($item, $view, $this->options);
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                if (null !== $line = $this->buildSaleItemLineView($child, $level + 1)) {
                    $view->addLine($line);
                }
            }
        }

        return $view;
    }

    /**
     * Builds the sale discount line view.
     *
     * @param Model\SaleAdjustmentInterface $adjustment
     * @param int                           $level
     *
     * @return LineView
     */
    private function buildDiscountLine(Model\SaleAdjustmentInterface $adjustment, $level = 0)
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

        $result = $adjustment->getResult();

        $view
            ->setDesignation($designation)
            ->setBase($this->formatter->currency($result->getBase()))
            ->setTaxAmount($this->formatter->currency($result->getTax()))
            ->setTotal($this->formatter->currency($result->getTotal()));

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
        if (0 >= $sale->getShipmentAmount() && !$this->options['private']) {
            return;
        }

        $lineNumber = $this->lineNumber++;

        $view = new LineView(
            'shipment',
            'shipment',
            $lineNumber,
            0
        );

        // Method title
        $designation = 'Shipping cost';
        if (null !== $method = $sale->getShipmentMethod()) {
            $designation = $method->getTitle();
        }

        // Total weight
        $designation .= ' (' . $this->formatter->number($sale->getWeightTotal()) . ' kg)';

        $result = $sale->getShipmentResult();

        $view
            ->setDesignation($designation)
            ->setBase($this->formatter->currency($result->getBase()))
            ->setTaxRates($this->formatter->rates(...$result->getTaxAdjustments()))
            ->setTaxAmount($this->formatter->currency($result->getTax()))
            ->setTotal($this->formatter->currency($result->getTotal()));

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

<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Model\Adjustment;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
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
     * @var AmountCalculatorFactory
     */
    private $amountCalculatorFactory;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var MarginCalculatorFactory
     */
    private $marginCalculatorFactory;

    /**
     * @var FormatterFactory
     */
    private $formatterFactory;

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
     * @var AmountCalculatorInterface
     */
    private $amountCalculator;

    /**
     * @var MarginCalculatorInterface
     */
    private $marginCalculator;


    /**
     * Constructor.
     *
     * @param ViewTypeRegistryInterface  $registry
     * @param AmountCalculatorFactory    $amountCalculatorFactory
     * @param MarginCalculatorFactory    $marginCalculatorFactory
     * @param CurrencyConverterInterface $currencyConverter
     * @param FormatterFactory           $formatterFactory
     * @param string                     $defaultTemplate
     * @param string                     $editableTemplate
     */
    public function __construct(
        ViewTypeRegistryInterface $registry,
        AmountCalculatorFactory $amountCalculatorFactory,
        MarginCalculatorFactory $marginCalculatorFactory,
        CurrencyConverterInterface $currencyConverter,
        FormatterFactory $formatterFactory,
        $defaultTemplate = '@Commerce/Sale/view.html.twig',
        $editableTemplate = '@Commerce/Sale/view_editable.html.twig'
    ) {
        $this->registry = $registry;
        $this->amountCalculatorFactory = $amountCalculatorFactory;
        $this->marginCalculatorFactory = $marginCalculatorFactory;
        $this->currencyConverter = $currencyConverter;
        $this->formatterFactory = $formatterFactory;
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
    public function buildSaleView(Model\SaleInterface $sale, array $options = []): SaleView
    {
        $this->initialize($sale, $options);

        $c = $this->view->getCurrency();

        $this->amountCalculator = $this->amountCalculatorFactory->create($c);
        $this->marginCalculator = $this->marginCalculatorFactory->create($c, true);

        // Gross total view
        $grossResult = $this->amountCalculator->calculateSale($sale, true);
        $this->view->setGross(new TotalView(
            $this->currency($grossResult->getGross($this->view->isAti())),
            $this->currency($grossResult->getDiscount($this->view->isAti())),
            $this->currency($grossResult->getBase($this->view->isAti()))
        ));

        // Final total view
        $finalResult = $this->amountCalculator->calculateSale($sale);
        $this->view->setFinal(new TotalView(
            $this->currency($finalResult->getBase()),
            $this->currency($finalResult->getTax()),
            $this->currency($finalResult->getTotal())
        ));

        if ($this->options['private'] && $margin = $this->marginCalculator->calculateSale($sale)) {
            $prefix = $margin->isAverage() ? '~' : '';

            $amount = $this->currencyConverter->convertWithSubject($margin->getAmount(), $sale, $c);

            $this->view->setMargin(new MarginView(
                $this->currency($amount, $prefix),
                $this->percent($margin->getPercent(), $prefix)
            ));
            $this->view->vars['show_margin'] = true;
        }

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
        if ($this->view->vars['show_availability']) {
            $columnsCount++;
        }

        $this->view->vars['show_discounts'] =
            $this->options['discounts'] || 0 < count($grossResult->getDiscountAdjustments());
        if ($this->view->vars['show_discounts']) {
            $columnsCount += 3;
        }

        $this->view->vars['show_taxes'] =
            $this->options['taxes'] || 1 < count($finalResult->getTaxAdjustments());
        if ($this->view->vars['show_taxes']) {
            $columnsCount++;
        }
        if ($this->view->vars['show_margin']) {
            $columnsCount++;
        }
        if ($this->options['editable']) {
            $columnsCount++;
        }
        $this->view->vars['columns_count'] = $columnsCount;
        $this->view->vars['private'] = $this->options['private'];

        return $this->view;
    }

    /**
     * Initializes the view builder.
     *
     * @param Model\SaleInterface $sale
     * @param array               $options
     */
    private function initialize(Model\SaleInterface $sale, array $options = []): void
    {
        $this->lineNumber = 1;
        $this->view = new SaleView();

        $this->types = $this->registry->getTypesForSale($sale);

        foreach ($this->types as $type) {
            $type->configureOptions($sale, $this->view, $options);
        }

        $this->options = $this->getOptionsResolver()->resolve($options);

        $this
            ->view
            ->setTemplate($this->options['template'])
            ->setAti($this->options['ati'])
            ->setLocale($this->options['locale'])
            ->setCurrency($this->options['currency']);

        $this->formatter = $this->formatterFactory->create($this->options['locale'], $this->options['currency']);

        foreach ($this->types as $type) {
            $type->setFormatter($this->formatter);
        }
    }

    /**
     * Builds the sale taxes views.
     *
     * @param Model\SaleInterface $sale
     */
    private function buildSaleTaxesViews(Model\SaleInterface $sale): void
    {
        if (!$this->options['taxes_view']) {
            return;
        }

        $amounts = $this->amountCalculator->calculateSale($sale);

        foreach ($amounts->getTaxAdjustments() as $tax) {
            $this->view->addTax(new TaxView(
                $tax->getName(),
                $this->currency($tax->getAmount())
            ));
        }
    }

    /**
     * Builds the sale lines views.
     *
     * @param Model\SaleInterface $sale
     */
    private function buildSaleItemsLinesViews(Model\SaleInterface $sale): void
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
    private function buildSaleDiscountsLinesViews(Model\SaleInterface $sale): void
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
    private function buildSaleItemLineView(Model\SaleItemInterface $item, $level = 0): ?LineView
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

        $view->addClass('level-' . $level);

        $result = $this->amountCalculator->calculateSaleItem($item);

        $unit = $gross = $discountRates = $discountAmount = $base = $taxRates = $taxAmount = $total = null;

        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $ati = $this->view->isAti();
            $unit = $this->currency($result->getUnit($ati));
            $gross = $this->currency($result->getGross($ati));
            $discountRates = $this->rates(...$result->getDiscountAdjustments());
            if (0 < $discount = $result->getDiscount($ati)) {
                $discountAmount = $this->currency($discount);
            }
            $base = $this->currency($result->getBase($ati));
            $taxRates = $this->rates(...$result->getTaxAdjustments());
            if (0 < $tax = $result->getTax()) {
                $taxAmount = $this->currency($tax);
            }
            $total = $this->currency($result->getTotal());
        }

        // TODO Use packaging format
        if ($item->isPrivate()) {
            $quantity = sprintf(
                '%s (x%s)',
                $this->number($item->getQuantity()),
                $this->number($item->getParentsQuantity())
            );
        } else {
            $quantity = $this->number($item->getTotalQuantity());
        }

        if (
            $this->options['private']
            || !($item->isConfigurable() && $item->isCompound() && !$item->hasPrivateChildren())
        ) {
            $view->setReference($item->getReference());
        }

        $view
            ->setDesignation($item->getDesignation())
            ->setDescription($item->getDescription())
            ->setUnit($unit)
            ->setQuantity($quantity)
            ->setGross($gross)
            ->setDiscountRates($discountRates)
            ->setDiscountAmount($discountAmount)
            ->setBase($base)
            ->setTaxRates($taxRates)
            ->setTaxAmount($taxAmount)
            ->setTotal($total)
            ->setSource($item);

        if ($item->isPrivate()) {
            $view->setPrivate(true)->addClass('private');
        } else {
            $view->setPrivate(false)->removeClass('private');
        }

        foreach ($this->types as $type) {
            $type->buildItemView($item, $view, $this->options);
        }

        if (!empty($view->getAvailability())) {
            $this->view->vars['show_availability'] = true;
        }

        if ($this->view->vars['show_margin'] && !($item->isCompound() && !$item->hasPrivateChildren())) {
            if ($margin = $this->marginCalculator->calculateSaleItem($item)) {
                $view->setMargin(
                    $this->percent($margin->getPercent(), $margin->isAverage() ? '~' : '')
                );
            }
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
    private function buildDiscountLine(Model\SaleAdjustmentInterface $adjustment, $level = 0): LineView
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
                $designation .= $this->percent($adjustment->getAmount());
            }
        }

        $result = $this->amountCalculator->calculateSaleDiscount($adjustment);

        $view
            ->setDesignation($designation)
            ->setBase($this->currency($result->getBase()))
            ->setTaxAmount($this->currency($result->getTax()))
            ->setTotal($this->currency($result->getTotal()))
            ->setSource($adjustment);

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
    private function buildShipmentLine(Model\SaleInterface $sale): void
    {
        if (null === $sale->getShipmentMethod() && !$this->options['private']) {
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
        if (!empty($label = $sale->getShipmentLabel())) {
            $designation = $label;
        } elseif (null !== $method = $sale->getShipmentMethod()) {
            $designation = $method->getTitle();
        }

        // Shipment weight
        $designation .= ' (' . $this->number($sale->getShipmentWeight() ?? $sale->getWeightTotal()) . ' kg)';

        $result = $this->amountCalculator->calculateSaleShipment($sale);

        $view
            ->setDesignation($designation)
            ->setBase($this->currency($result->getBase()))
            ->setTaxRates($this->rates(...$result->getTaxAdjustments()))
            ->setTaxAmount($this->currency($result->getTax()))
            ->setTotal($this->currency($result->getTotal()))
            ->setSource($sale);

        foreach ($this->types as $type) {
            $type->buildShipmentView($sale, $view, $this->options);
        }

        $this->view->setShipment($view);
    }

    /**
     * Formats currency as needed.
     *
     * @param float  $amount
     * @param string $prefix
     *
     * @return string
     */
    private function currency(float $amount, string $prefix = ''): string
    {
        if ($this->options['export']) {
            return (string)round($amount, 5);
        }

        return $prefix . $this->formatter->currency($amount);
    }

    /**
     * Formats percent as needed.
     *
     * @param float  $amount
     * @param string $prefix
     *
     * @return string
     */
    private function percent(float $amount, string $prefix = ''): string
    {
        if ($this->options['export']) {
            return (string)round($amount, 2);
        }

        return $prefix . $this->formatter->percent($amount);
    }

    /**
     * Formats number as needed.
     *
     * @param float  $value
     * @param string $prefix
     *
     * @return string
     */
    private function number(float $value, string $prefix = ''): string
    {
        if ($this->options['export']) {
            return (string)round($value, 2);
        }

        return $prefix . $this->formatter->number($value);
    }

    /**
     * Formats adjustments rates as needed.
     *
     * @param Adjustment ...$adjustments
     *
     * @return string
     */
    private function rates(Adjustment ...$adjustments): string
    {
        if ($this->options['export']) {
            $rate = 0;
            if (!empty($adjustments)) {
                $rate = reset($adjustments)->getRate() / 100;
                foreach (array_slice($adjustments, 1) as $adjustment) {
                    $rate *= 1 + $adjustment->getRate() / 100;
                }
            }

            return (string)round($rate, 2);
        }

        return $this->formatter->rates(...$adjustments);
    }

    /**
     * Returns the options resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionsResolver(): OptionsResolver
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
                'currency'   => $this->currencyConverter->getDefaultCurrency(),
                'ati'        => false,
                'export'     => false,
                'discounts'  => null,
                'taxes'      => null,
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
            ->setAllowedTypes('currency', 'string')
            ->setAllowedTypes('ati', 'bool')
            ->setAllowedTypes('export', 'bool')
            ->setAllowedTypes('discounts', ['null', 'bool'])
            ->setAllowedTypes('taxes', ['null', 'bool'])
            ->setAllowedTypes('template', ['null', 'string'])
            ->setNormalizer('ati', function (Options $options, $value) {
                if (true === $options['export']) {
                    return false;
                }

                return $value;
            })
            ->setNormalizer('discounts', function (Options $options, $value) {
                if (true === $options['export']) {
                    return true;
                }

                return $value;
            })
            ->setNormalizer('taxes', function (Options $options, $value) {
                if (true === $options['export']) {
                    return true;
                }

                return $value;
            });

        return $this->optionsResolver = $resolver;
    }
}

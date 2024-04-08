<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Helper\SaleItemHelper;
use Ekyna\Component\Commerce\Common\Helper\ViewHelper;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Model\Adjustment;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Locale;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function sprintf;

/**
 * Class ViewBuilder
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ViewBuilder
{
    private ?OptionsResolver $optionsResolver = null;
    private array            $options;
    private SaleView         $view;
    /** @var array<int, ViewTypeInterface> */
    private array                     $types;
    private int                       $lineNumber;
    private Formatter                 $formatter;
    private ViewHelper                $viewHelper;
    private AmountCalculatorInterface $amountCalculator;
    private MarginCalculatorInterface $marginCalculator;

    public function __construct(
        private readonly ViewTypeRegistryInterface  $registry,
        private readonly AmountCalculatorFactory    $amountCalculatorFactory,
        private readonly MarginCalculatorFactory    $marginCalculatorFactory,
        private readonly CurrencyConverterInterface $currencyConverter,
        private readonly WeightCalculatorInterface  $weightCalculator,
        private readonly FormatterFactory           $formatterFactory,
        private readonly SaleItemHelper             $saleItemHelper,
        private readonly string                     $defaultTemplate = '@Commerce/Sale/view.html.twig',
        private readonly string                     $editableTemplate = '@Commerce/Sale/view_editable.html.twig'
    ) {
    }

    /**
     * Builds the sale view.
     */
    public function buildSaleView(Model\SaleInterface $sale, array $options = []): SaleView
    {
        $this->initialize($sale, $options);

        $c = $this->view->currency;

        $this->amountCalculator = $this->amountCalculatorFactory->create($c);
        $this->marginCalculator = $this->marginCalculatorFactory->create($c);

        // Gross total view
        $grossResult = $this->amountCalculator->calculateSale($sale, true);
        $this->view->gross = new TotalView(
            $this->currency($grossResult->getGross($this->view->ati)),
            $this->currency($grossResult->getDiscount($this->view->ati)),
            $this->currency($grossResult->getBase($this->view->ati))
        );

        // Final total view
        $finalResult = $this->amountCalculator->calculateSale($sale);
        $this->view->final = new TotalView(
            $this->currency($finalResult->getBase()),
            $this->currency($finalResult->getTax()),
            $this->currency($finalResult->getTotal())
        );

        if ($this->options['private']) {
            // TODO if ($sale instanceof MarginSubjectInterface) $margin = $sale->getMargin()
            $margin = $this->marginCalculator->calculateSale($sale);
            $this->view->vars['show_margin'] = true;

            // Gross margin
            $prefix = $margin->isAverage() ? '~' : '';

            $amount = $this
                ->currencyConverter
                ->convertWithSubject($margin->getTotal(true), $sale, $c);

            $this->view->grossMargin = new MarginView(
                $this->currency($amount, $prefix),
                $this->percent($margin->getPercent(true), $prefix)
            );

            // Net margin
            $prefix = $margin->isAverage() ? '~' : '';

            $amount = $this
                ->currencyConverter
                ->convertWithSubject($margin->getTotal(false), $sale, $c);

            $this->view->netMargin = new MarginView(
                $this->currency($amount, $prefix),
                $this->percent($margin->getPercent(false), $prefix)
            );
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
            $batchableCount = 0;
            foreach ($this->view->getItems() as $line) {
                if (!$line->batchable) {
                    continue;
                }
                $batchableCount++;
                if (1 < $batchableCount) {
                    $this->view->vars['show_batch'] = true;
                    break;
                }
            }
        }
        if ($this->view->vars['show_batch']) {
            $columnsCount++;
        }
        $this->view->vars['columns_count'] = $columnsCount;
        $this->view->vars['private'] = $this->options['private'];

        return $this->view;
    }

    /**
     * Initializes the view builder.
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

        $this->view->locale = $this->options['locale'];
        $this->view->currency = $this->options['currency'];
        $this->view->template = $this->options['template'];
        $this->view->ati = $this->options['ati'];

        $this->formatter = $this->formatterFactory->create($this->options['locale'], $this->options['currency']);
        $this->viewHelper = new ViewHelper($this->formatter);

        foreach ($this->types as $type) {
            $type->setFormatter($this->formatter);
        }
    }

    /**
     * Builds the sale taxes views.
     */
    private function buildSaleTaxesViews(Model\SaleInterface $sale): void
    {
        if (!$this->options['taxes_view']) {
            return;
        }

        $amounts = $this->amountCalculator->calculateSale($sale);

        foreach ($amounts->getTaxAdjustments() as $tax) {
            $this->view->addTax(
                new TaxView(
                    $tax->getName(),
                    $this->currency($tax->getAmount())
                )
            );
        }

        foreach ($amounts->getIncludedAdjustments() as $include) {
            $this->view->addInclude(
                new TaxView(
                    $include->getName(),
                    $this->currency($include->getAmount())
                )
            );
        }
    }

    /**
     * Builds the sale lines views.
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
     */
    private function buildSaleItemLineView(Model\SaleItemInterface $item, int $level = 0): ?LineView
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

        $unit = $gross = $discountRates = $discountAmount = $base = $includes = $taxRates = $taxAmount = $total = null;

        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $ati = $this->view->ati;
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
            $includes = $this->viewHelper->buildIncludesDescription($result, $item->getQuantity());
            $total = $this->currency($result->getTotal());
        }

        // TODO Use packaging format
        if ($item->isPrivate()) {
            $quantity = sprintf(
                '%s (x%s)',
                $this->number($item->getQuantity()),
                $this->number($item->getParentsQuantity())
            );
            $weight = '';
        } else {
            $quantity = $this->number($item->getTotalQuantity());
            $weight = $this->number($this->weightCalculator->calculateSaleItem($item));
        }

        if (
            $this->options['private']
            || !($item->isConfigurable() && $item->isCompound() && !$item->hasPrivateChildren())
        ) {
            $view->reference = $item->getReference();
        }

        $view->designation = $item->getDesignation();
        $view->description = $item->getDescription();
        $view->unit = $unit;
        $view->quantity = $quantity;
        $view->gross = $gross;
        $view->discountRates = $discountRates;
        $view->discountAmount = $discountAmount;
        $view->base = $base;
        $view->includes = $includes;
        $view->taxRates = $taxRates;
        $view->taxAmount = $taxAmount;
        $view->total = $total;
        $view->source = $item;
        $view->weight = $weight;

        if ($view->private = $item->isPrivate()) {
            $view->addClass('private');
        } else {
            $view->removeClass('private');
        }

        foreach ($this->types as $type) {
            $type->buildItemView($item, $view, $this->options);
        }

        if (!empty($view->availability)) {
            $this->view->vars['show_availability'] = true;
        }

        if ($this->view->vars['show_margin'] && !($item->isCompound() && !$item->hasPrivateChildren())) {
            $margin = $this->marginCalculator->calculateSaleItem($item, $item->isPrivate());
            $view->margin = $this->percent($margin->getPercent(false), $margin->isAverage() ? '~' : '');
        }

        if (!$item->hasParent()) {
            if (!$this->saleItemHelper->isShippedOrInvoiced($item)) {
                $view->batchable = true;
            }
            // TODO Append included taxes detail to description
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
     */
    private function buildDiscountLine(Model\SaleAdjustmentInterface $adjustment): LineView
    {
        if (Model\AdjustmentTypes::TYPE_DISCOUNT !== $adjustment->getType()) {
            throw new InvalidArgumentException('Unexpected adjustment type.');
        }

        $lineNumber = $this->lineNumber++;

        $view = new LineView(
            'adjustment_' . ($lineNumber - 1),
            'adjustment_' . $adjustment->getId(),
            $lineNumber,
            0
        );

        if (empty($designation = $adjustment->getDesignation())) {
            $designation = 'Discount ';
            if ($adjustment->getMode() === Model\AdjustmentModes::MODE_PERCENT) {
                $designation .= $this->percent($adjustment->getAmount());
            }
        }

        $result = $this->amountCalculator->calculateSaleDiscount($adjustment);

        $view->designation = $designation;
        $view->base = $this->currency($result->getBase());
        $view->taxAmount = $this->currency($result->getTax());
        $view->total = $this->currency($result->getTotal());
        $view->source = $adjustment;

        foreach ($this->types as $type) {
            $type->buildAdjustmentView($adjustment, $view, $this->options);
        }

        return $view;
    }

    /**
     * Builds the shipment adjustment line view.
     */
    private function buildShipmentLine(Model\SaleInterface $sale): void
    {
        if (null === $sale->getShipmentMethod() && !$this->options['private']) {
            $this->view->shipment = null;

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

        $view->designation = $designation;
        $view->base = $this->currency($result->getBase());
        $view->taxRates = $this->rates(...$result->getTaxAdjustments());
        $view->taxAmount = $this->currency($result->getTax());
        $view->total = $this->currency($result->getTotal());
        $view->source = $sale;

        foreach ($this->types as $type) {
            $type->buildShipmentView($sale, $view, $this->options);
        }

        if ($this->view->vars['show_margin']) {
            $margin = $this->marginCalculator->calculateSaleShipment($sale);
            $view->margin = $this->percent($margin->getPercent(false), $margin->isAverage() ? '~' : '');
        }

        $this->view->shipment = $view;
    }

    /**
     * Formats currency.
     */
    private function currency(Decimal $amount, string $prefix = ''): string
    {
        if ($this->options['export']) {
            return $amount->toFixed(5);
        }

        return $prefix . $this->formatter->currency($amount);
    }

    /**
     * Formats percent.
     */
    private function percent(Decimal $amount, string $prefix = ''): string
    {
        if ($this->options['export']) {
            return $amount->toFixed(2);
        }

        return $prefix . $this->formatter->percent($amount);
    }

    /**
     * Formats number.
     */
    private function number(Decimal $value): string
    {
        if ($this->options['export']) {
            return $value->toFixed(2);
        }

        return $this->formatter->number($value);
    }

    /**
     * Formats adjustments rates.
     */
    private function rates(Adjustment ...$adjustments): string
    {
        if ($this->options['export']) {
            $rate = new Decimal(0);
            if (!empty($adjustments)) {
                $rate = reset($adjustments)->getRate() / 100;
                foreach (array_slice($adjustments, 1) as $adjustment) {
                    $rate *= 1 + $adjustment->getRate() / 100;
                }
            }

            return $rate->toFixed(2);
        }

        return $this->formatter->rates(...$adjustments);
    }

    /**
     * Returns the options resolver.
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
                'locale'     => Locale::getDefault(),
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

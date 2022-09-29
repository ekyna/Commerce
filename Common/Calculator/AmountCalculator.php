<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;

/**
 * Class AmountCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AmountCalculator implements AmountCalculatorInterface
{
    private readonly CurrencyConverterInterface        $currencyConverter;
    private readonly InvoiceSubjectCalculatorInterface $invoiceCalculator;
    private readonly AmountCalculatorFactory           $amountCalculatorFactory;

    /** @var Model\Amount[] */
    private array $cache = [];

    /**
     * @internal Use Calculator factory
     */
    public function __construct(
        private readonly string      $currency,
        private readonly bool        $profit,
        private readonly ?StatFilter $filter
    ) {
    }

    public function clear(): void
    {
        $this->cache = [];
    }

    public function setCurrencyConverter(CurrencyConverterInterface $converter): void
    {
        $this->currencyConverter = $converter;
    }

    public function setInvoiceCalculator(InvoiceSubjectCalculatorInterface $calculator): void
    {
        $this->invoiceCalculator = $calculator;
    }

    public function setAmountCalculatorFactory(AmountCalculatorFactory $factory): void
    {
        $this->amountCalculatorFactory = $factory;
    }

    public function calculateSale(Model\SaleInterface $sale, bool $asGross = false): Model\Amount
    {
        $key = spl_object_hash($sale);
        if ($result = $this->get($key . ($asGross ? '_gross' : '_final'))) {
            return $result;
        }

        // Items / Gross result
        if (null === $gross = $this->get($key . '_gross')) {
            $gross = clone $this->calculateSaleItems($sale);
            $this->set($key . '_gross', $gross);
        }

        // Final result
        $final = Model\Amount::createFinalFromGross($gross);
        $this->set($key . '_final', $final);

        // Discounts
        if ($sale->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $this->calculateSaleDiscount($adjustment, $gross, $final);
            }
        }

        // Shipment
        $this->calculateSaleShipment($sale, $final);

        // Round/finalize results.
        $gross->round();
        $final->finalize();

        return $asGross ? $gross : $final;
    }

    public function calculateSaleItems(Model\SaleInterface $sale): Model\Amount
    {
        $key = spl_object_hash($sale) . '_items';
        if ($result = $this->get($key)) {
            return $result;
        }

        $result = new Model\Amount($this->currency);
        $this->set($key, $result);

        // Sum public
        foreach ($sale->getItems() as $item) {
            if ($this->isItemSkipped($item)) {
                continue;
            }

            if ($item->isPrivate()) {
                throw new Exception\LogicException('Root sale items can\'t be private.');
            }

            // Skip compound with only public children
            // TODO Removed (check) if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $result->merge($this->calculateSaleItem($item));
            //}

            $this->mergeItemsResults($item, $result);
        }

        // Set unit = gross
        $result->copyGrossToUnit();

        return $result;
    }

    /**
     * @TODO use packaging format on quantities
     */
    public function calculateSaleItem(
        Model\SaleItemInterface $item,
        Decimal                 $quantity = null,
        bool                    $asPublic = false
    ): Model\Amount {
        if ($quantity) {
            if ($this->profit) {
                throw new Exception\LogicException('You can\'t override quantity if revenue mode is enabled.');
            }

            if ($quantity->isNegative()) {
                throw new Exception\InvalidArgumentException('Specific quantity must be greater than or equal to zero.');
            }
        }

        $key = spl_object_hash($item) . ($quantity ? "_$quantity" : '');
        if ($result = $this->get($key)) {
            return $result;
        }

        $sale = $item->getRootSale();
        $ati = $sale->isAtiDisplayMode();

        $taxGroup = $item->getTaxGroup();

        // Round unit price only for 'net' calculation
        $unit = $this
            ->currencyConverter
            ->convertWithSubject($item->getNetPrice(), $sale, $this->currency, !$ati);

        // Add private items unit prices
        foreach ($item->getChildren() as $child) {
            if ($this->isItemSkipped($child)) {
                continue;
            }

            $q = $quantity ? $child->getQuantity()->mul($quantity) : null;
            $childResult = $this->calculateSaleItem($child, $q);

            if (!$child->isPrivate()) {
                continue;
            }

            if ($taxGroup !== $child->getTaxGroup()) {
                throw new Exception\LogicException('Private item must have the same tax group as its parent.');
            }

            if ($child->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
                throw new Exception\LogicException('Private items can\'t have discount adjustment.');
            }

            $unit += $ati
                ? $childResult->getUnit()->mul($child->getQuantity())
                : Money::round($childResult->getUnit(), $this->currency)->mul($child->getQuantity());
        }

        $quantity = $quantity ?? $this->calculateSaleItemQuantity($item);

        if ($item->getRootSale()->isSample()) {
            // Sample sale case : zero amounts
            $result = new Model\Amount($this->currency);
        } elseif ($item->isPrivate() && !$asPublic) {
            // Private case : we just need unit amount
            $gross = $unit->mul($quantity);
            $result = new Model\Amount($this->currency, $unit, $gross, null, $gross);
        } else {
            // Regular case
            $discounts = $taxes = [];
            $discount = new Decimal(0);
            $tax = new Decimal(0);

            // Gross price
            $gross = $unit->mul($quantity);

            $parent = $item->getParent();
            $discountAdjustments = $item->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)->toArray();

            // Items without subject inherit discounts from their non-compound parent
            if (empty($discountAdjustments)) {
                if (!$item->hasSubjectIdentity() && $parent && !$parent->isCompound()) {
                    $discountAdjustments = $parent->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)->toArray();
                } elseif ($item->isPrivate() && $asPublic) {
                    $discountAdjustments = $item
                        ->getPublicParent()
                        ->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)->toArray();
                }
            }

            // Discount amount and result adjustments
            $discountBase = $gross;
            foreach ($discountAdjustments as $data) {
                $adjustment = $this->createPercentAdjustment($data, $discountBase);
                $discountBase -= $adjustment->getAmount();
                $discount += $adjustment->getAmount();
                $discounts[] = $adjustment;
            }

            // Base
            $base = $gross->sub($discount);
            $base = $ati ? $base->round(5) : Money::round($base, $this->currency);

            // Tax amount and result adjustments
            if ($item->isPrivate() && $asPublic) {
                $taxAdjustments = $item
                    ->getPublicParent()
                    ->getAdjustments(Model\AdjustmentTypes::TYPE_TAXATION)->toArray();
            } else {
                $taxAdjustments = $item->getAdjustments(Model\AdjustmentTypes::TYPE_TAXATION)->toArray();
            }

            foreach ($taxAdjustments as $data) {
                $adjustment = $this->createPercentAdjustment($data, $base);

                $tax += $adjustment->getAmount();
                $taxes[] = $adjustment;
            }

            // Total
            $total = Money::round($base + $tax, $this->currency);

            // Result
            $result = new Model\Amount(
                $this->currency,
                $unit, $gross, $discount, $base,
                $tax, $total, $discounts, $taxes
            );
        }

        if ($ati) {
            $result->round();
        }

        $this->set($key, $result);

        return $result;
    }

    public function calculateSaleDiscount(
        Model\SaleAdjustmentInterface $adjustment,
        Model\Amount                  $gross = null,
        Model\Amount                  $final = null
    ): Model\Amount {
        $this->assertAdjustmentType($adjustment, Model\AdjustmentTypes::TYPE_DISCOUNT);

        $key = spl_object_hash($adjustment);
        if ($result = $this->get($key)) {
            return $result;
        }

        /** @var Model\SaleInterface $sale */
        $sale = $adjustment->getAdjustable();

        if (!$gross && !$gross = $this->get(spl_object_hash($adjustment->getSale()) . '_items')) {
            throw new Exception\LogicException('Failed to retrieve sale gross result.');
        }

        $this->set($key, $result = new Model\Amount($this->currency));

        // Sample sale case
        if ($sale->isSample()) {
            return $result;
        }

        // Revenue mode
        if ($this->profit && $sale instanceof InvoiceSubjectInterface) {
            if ($this->invoiceCalculator->calculateSoldQuantity($adjustment)->isZero()) {
                return $result;
            }
        }

        $base = $gross->getBase();

        $mode = $adjustment->getMode();
        if (Model\AdjustmentModes::MODE_PERCENT === $mode) {
            $rate = $adjustment->getAmount();

            $result->addUnit(Money::round($base * $rate / 100, $this->currency));

            foreach ($gross->getTaxAdjustments() as $tax) {
                $amount = Money::round($tax->getAmount() * $rate / 100, $this->currency);
                $result->addTaxAdjustment(new Model\Adjustment($tax->getName(), $amount, $tax->getRate()));
                $result->addTax($amount);
            }
        } elseif (Model\AdjustmentModes::MODE_FLAT === $mode) {
            $realBase = $this->getRealGrossBase($sale);

            $amount = $this
                ->currencyConverter
                ->convertWithSubject($adjustment->getAmount(), $sale, $this->currency, false);

            if ($realBase > $base) {
                $unit = Money::round($amount * $base / $realBase, $this->currency);
            } else {
                $unit = $amount;
            }
            $result->addUnit($unit);

            foreach ($gross->getTaxAdjustments() as $tax) {
                $amount = Money::round($unit * $tax->getAmount() / $base, $this->currency);
                $result->addTaxAdjustment(new Model\Adjustment($tax->getName(), $amount, $tax->getRate()));
                $result->addTax($amount);
            }
        } else {
            throw new Exception\InvalidArgumentException("Unexpected adjustment mode '$mode'.");
        }

        $result->addGross(clone $result->getUnit());
        $result->addBase(clone $result->getUnit());
        $result->addTotal($result->getUnit() + $result->getTax());

        if (!$final) {
            return $result;
        }

        // Add to final result
        $final->addDiscount(clone $result->getBase());
        $final->addBase($result->getBase()->negate());
        $final->addTotal($result->getBase()->negate());
        $final->addDiscountAdjustment(new Model\Adjustment(
            $adjustment->getDesignation() ?: '',
            clone $result->getBase(),
            $adjustment->getMode() === Model\AdjustmentModes::MODE_PERCENT ? $adjustment->getAmount() : null
        ));

        foreach ($result->getTaxAdjustments() as $a) {
            $final->addTaxAdjustment(new Model\Adjustment($a->getName(), $a->getAmount()->negate(), $a->getRate()));
            $final->addTax($a->getAmount()->negate());
            $final->addTotal($a->getAmount()->negate());
        }

        if ($sale->isAtiDisplayMode()) {
            $result->round();
        }

        return $result;
    }

    public function calculateSaleShipment(
        Model\SaleInterface $sale,
        Model\Amount        $final = null
    ): Model\Amount {
        $key = spl_object_hash($sale) . '_shipment';
        if ($result = $this->get($key)) {
            return $result;
        }

        $this->set($key, $result = new Model\Amount($this->currency));

        // Sample sale case
        if ($sale->isSample()) {
            return $result;
        }

        // Revenue mode
        if ($this->profit && $sale instanceof InvoiceSubjectInterface) {
            if ($this->invoiceCalculator->calculateSoldQuantity($sale)->isZero()) {
                return $result;
            }
        }

        // Abort if shipment cost is lower than or equals zero
        $base = $this
            ->currencyConverter
            ->convertWithSubject($sale->getShipmentAmount(), $sale, $this->currency, false);

        $result->addUnit($base);
        $result->addGross($base);
        $result->addBase($base);
        $result->addTotal($base);

        if (0 < $base) {
            // Shipment taxation
            foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_TAXATION) as $data) {
                $adjustment = $this->createPercentAdjustment($data, $base);

                $result->addTaxAdjustment($adjustment);
                $result->addTax($adjustment->getAmount());
                $result->addTotal($adjustment->getAmount());
            }

            if ($final) {
                // Add to final result
                $final->addBase($result->getBase());
                $final->addTotal($result->getBase());

                foreach ($result->getTaxAdjustments() as $a) {
                    $final->addTaxAdjustment($a);
                    $final->addTax($a->getAmount());
                    $final->addTotal($a->getAmount());
                }
            }
        }

        if ($sale->isAtiDisplayMode()) {
            $result->round();
        }

        return $result;
    }

    /**
     * Calculates the sale item quantity.
     */
    protected function calculateSaleItemQuantity(Model\SaleItemInterface $item): Decimal
    {
        if (!$this->profit) {
            return $item->getTotalQuantity();
        }

        if ($item instanceof StockAssignmentsInterface && $item->hasStockAssignments()) {
            $quantity = new Decimal('0');
            foreach ($item->getStockAssignments() as $assignment) {
                $quantity += $assignment->getSoldQuantity();
            }

            return $quantity;
        }

        if ($item->getRootSale() instanceof InvoiceSubjectInterface) {
            return $this->invoiceCalculator->calculateSoldQuantity($item);
        }

        return $item->getTotalQuantity();
    }

    /**
     * Creates a new result adjustment.
     */
    protected function createPercentAdjustment(Model\AdjustmentInterface $data, Decimal $base): Model\Adjustment
    {
        $this->assertAdjustmentMode($data, Model\AdjustmentModes::MODE_PERCENT);

        $rate = $data->getAmount();

        if ($data->getType() === Model\AdjustmentTypes::TYPE_TAXATION) {
            // Calculate taxation as ATI - NET
            $amount = Money::round($base * (1 + $rate / 100), $this->currency) - Money::round($base, $this->currency);
        } else {
            $amount = Money::round($base * $rate / 100, $this->currency);
        }

        return new Model\Adjustment($data->getDesignation() ?: '', $amount, $rate);
    }

    /**
     * Asserts that the adjustment mode is as expected.
     */
    protected function assertAdjustmentMode(Model\AdjustmentInterface $adjustment, string $expected): void
    {
        if ($expected === $mode = $adjustment->getMode()) {
            return;
        }

        throw new Exception\InvalidArgumentException("Unexpected adjustment mode '$mode'.");
    }

    /**
     * Returns whether the given item should be skipped regarding the configured filter.
     */
    protected function isItemSkipped(Model\SaleItemInterface $item): bool
    {
        if (!$this->filter) {
            return false;
        }

        if (!$item->hasSubjectIdentity()) {
            return !$this->filter->isExcludeSubjects();
        }

        return $this->filter->hasSubject($item->getSubjectIdentity()) xor !$this->filter->isExcludeSubjects();
    }

    /**
     * Merges the public children results recursively into the given result.
     */
    protected function mergeItemsResults(Model\SaleItemInterface $item, Model\Amount $result): void
    {
        // At this point, items result are calculated and set.
        foreach ($item->getChildren() as $child) {
            if ($child->isPrivate() || $this->isItemSkipped($child)) {
                continue;
            }

            // Skip compound with only public children
            if (!($child->isCompound() && !$child->hasPrivateChildren())) {
                $result->merge($this->calculateSaleItem($child));
            }

            if ($child->hasChildren()) {
                $this->mergeItemsResults($child, $result);
            }
        }
    }

    /**
     * Asserts that the adjustment type is as expected.
     */
    protected function assertAdjustmentType(Model\AdjustmentInterface $adjustment, string $expected): void
    {
        if ($expected === $type = $adjustment->getType()) {
            return;
        }

        throw new Exception\InvalidArgumentException("Unexpected adjustment type '$type'.");
    }

    /**
     * Calculates the real sale gross base amount.
     *
     * @throws Exception\LogicException
     */
    protected function getRealGrossBase(Model\SaleInterface $sale): Decimal
    {
        // Calculate real gross base
        return $this
            ->amountCalculatorFactory
            ->create($this->currency)
            ->calculateSaleItems($sale)
            ->getBase();
    }

    /**
     * Returns the cached amount if any.
     */
    protected function get(string $key): ?Model\Amount
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return null;
    }

    /**
     * Sets the cached amount.
     */
    protected function set(string $key, Model\Amount $amount): void
    {
        $this->cache[$key] = $amount;
    }
}

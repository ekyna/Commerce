<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

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
    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var InvoiceSubjectCalculatorInterface
     */
    private $invoiceCalculator;

    /**
     * @var AmountCalculatorFactory
     */
    private $amountCalculatorFactory;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var bool
     */
    private $revenue;

    /**
     * @var StatFilter
     */
    private $filter;

    /**
     * @var Model\Amount[]
     */
    private $cache;


    /**
     * Constructor.
     *
     * @param string          $currency
     * @param bool            $revenue
     * @param StatFilter|null $filter
     *
     * @internal Use Calculator factory
     */
    public function __construct(string $currency, bool $revenue, StatFilter $filter = null)
    {
        $this->currency = $currency;
        $this->revenue = $revenue;
        $this->filter = $filter;

        $this->clear();
    }

    /**
     * Clears the cache.
     */
    public function clear(): void
    {
        $this->cache = [];
    }

    /**
     * Sets the currency converter.
     *
     * @param CurrencyConverterInterface $converter
     */
    public function setCurrencyConverter(CurrencyConverterInterface $converter): void
    {
        $this->currencyConverter = $converter;
    }

    /**
     * Sets the invoice calculator.
     *
     * @param InvoiceSubjectCalculatorInterface $calculator
     */
    public function setInvoiceCalculator(InvoiceSubjectCalculatorInterface $calculator): void
    {
        $this->invoiceCalculator = $calculator;
    }

    /**
     * Sets the amount calculator factory.
     *
     * @param AmountCalculatorFactory $factory
     */
    public function setAmountCalculatorFactory(AmountCalculatorFactory $factory): void
    {
        $this->amountCalculatorFactory = $factory;
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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
                throw new Exception\LogicException("Root sale items can't be private.");
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
     * @inheritDoc
     *
     * @TODO use packaging format on quantities
     */
    public function calculateSaleItem(Model\SaleItemInterface $item, float $quantity = null, bool $asPublic = false): Model\Amount
    {
        if (null !== $quantity) {
            if ($this->revenue) {
                throw new Exception\LogicException("You can't override quantity if revenue mode is enabled.");
            }

            if (0 >= $quantity) {
                throw new Exception\InvalidArgumentException(
                    "Specific quantity must be greater than or equal to zero."
                );
            }
        }

        $key = spl_object_hash($item) . ($quantity ? "_$quantity" : '');
        if ($result = $this->get($key)) {
            return $result;
        }

        $sale = $item->getSale();
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

            $q = $quantity ? $quantity * $child->getQuantity() : null;
            $childResult = $this->calculateSaleItem($child, $q);

            if (!$child->isPrivate()) {
                continue;
            }

            if ($taxGroup !== $child->getTaxGroup()) {
                throw new Exception\LogicException("Private item must have the same tax group as its parent.");
            }

            if ($child->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
                throw new Exception\LogicException("Private items can't have discount adjustment.");
            }

            $unit += $ati
                ? $childResult->getUnit() * $child->getQuantity()
                : Money::round($childResult->getUnit(), $this->currency) * $child->getQuantity();
        }

        $quantity = $quantity ?? $this->calculateSaleItemQuantity($item);

        if ($item->getSale()->isSample()) {
            // Sample sale case : zero amounts
            $result = new Model\Amount($this->currency);
        } elseif ($item->isPrivate() && !$asPublic) {
            // Private case : we just need unit amount
            $gross = $unit * $quantity;
            $result = new Model\Amount($this->currency, $unit, $gross, 0, $gross);
        } else {
            // Regular case
            $discounts = $taxes = [];
            $discount = $tax = 0;

            // Gross price
            $gross = $unit * $quantity;

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
            $base = $ati
                ? round($gross - $discount, 5)
                : Money::round($gross - $discount, $this->currency);

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

    /**
     * @inheritDoc
     */
    public function calculateSaleDiscount(
        Model\SaleAdjustmentInterface $adjustment,
        Model\Amount $gross = null,
        Model\Amount $final = null
    ): Model\Amount {
        $this->assertAdjustmentType($adjustment, Model\AdjustmentTypes::TYPE_DISCOUNT);

        $key = spl_object_hash($adjustment);
        if ($result = $this->get($key)) {
            return $result;
        }

        /** @var Model\SaleInterface $sale */
        $sale = $adjustment->getAdjustable();

        if (!$gross && !$gross = $this->get(spl_object_hash($adjustment->getSale()) . '_items')) {
            throw new Exception\LogicException("Failed to retrieve sale gross result.");
        }

        $this->set($key, $result = new Model\Amount($this->currency));

        // Sample sale case
        if ($sale->isSample()) {
            return $result;
        }

        // Revenue mode
        if ($this->revenue && $sale instanceof InvoiceSubjectInterface) {
            if (0 >= $this->invoiceCalculator->calculateSoldQuantity($adjustment)) {
                return $result;
            }
        }

        $base = $gross->getBase();

        $mode = $adjustment->getMode();
        if (Model\AdjustmentModes::MODE_PERCENT === $mode) {
            $rate = (float)$adjustment->getAmount();

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
                ->convertWithSubject((float)$adjustment->getAmount(), $sale, $this->currency, false);

            if (1 === Money::compare($realBase, $base, $this->currency)) {
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

        $result->addGross($result->getUnit());
        $result->addBase($result->getUnit());
        $result->addTotal($result->getUnit() + $result->getTax());

        if (!$final) {
            return $result;
        }

        // Add to final result
        $final->addDiscount($result->getBase());
        $final->addBase(-$result->getBase());
        $final->addTotal(-$result->getBase());
        $final->addDiscountAdjustment(new Model\Adjustment(
            (string)$adjustment->getDesignation(),
            $result->getBase(),
            $adjustment->getMode() === Model\AdjustmentModes::MODE_PERCENT ? (float)$adjustment->getAmount() : 0
        ));

        foreach ($result->getTaxAdjustments() as $a) {
            $final->addTaxAdjustment(new Model\Adjustment($a->getName(), -$a->getAmount(), $a->getRate()));
            $final->addTax(-$a->getAmount());
            $final->addTotal(-$a->getAmount());
        }

        if ($sale->isAtiDisplayMode()) {
            $result->round();
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function calculateSaleShipment(
        Model\SaleInterface $sale,
        Model\Amount $final = null
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
        if ($this->revenue && $sale instanceof InvoiceSubjectInterface) {
            if (0 >= $this->invoiceCalculator->calculateSoldQuantity($sale)) {
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

        if (1 === Money::compare($base, 0, $this->currency)) {
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
     *
     * @param Model\SaleItemInterface $item
     *
     * @return float
     */
    protected function calculateSaleItemQuantity(Model\SaleItemInterface $item): float
    {
        if (!$this->revenue) {
            return $item->getTotalQuantity();
        }

        $quantity = 0.;

        if ($item instanceof StockAssignmentsInterface && $item->hasStockAssignments()) {
            foreach ($item->getStockAssignments() as $assignment) {
                $quantity += $assignment->getSoldQuantity();
            }

            return $quantity;
        }

        $sale = $item->getSale();
        if ($sale instanceof InvoiceSubjectInterface) {
            return $this->invoiceCalculator->calculateSoldQuantity($item);
        }

        return $item->getTotalQuantity();
    }

    /**
     * Creates a new result adjustment.
     *
     * @param Model\AdjustmentInterface $data
     * @param float                     $base
     *
     * @return Model\Adjustment
     */
    protected function createPercentAdjustment(Model\AdjustmentInterface $data, float $base): Model\Adjustment
    {
        $this->assertAdjustmentMode($data, Model\AdjustmentModes::MODE_PERCENT);

        $rate = (float)$data->getAmount();

        if ($data->getType() === Model\AdjustmentTypes::TYPE_TAXATION) {
            // Calculate taxation as ATI - NET
            $amount = Money::round($base * (1 + $rate / 100), $this->currency) - Money::round($base, $this->currency);
        } else {
            $amount = Money::round($base * $rate / 100, $this->currency);
        }

        return new Model\Adjustment((string)$data->getDesignation(), $amount, $rate);
    }

    /**
     * Asserts that the adjustment mode is as expected.
     *
     * @param Model\AdjustmentInterface $adjustment
     * @param string                    $expected
     */
    protected function assertAdjustmentMode(Model\AdjustmentInterface $adjustment, string $expected): void
    {
        if ($expected !== $mode = $adjustment->getMode()) {
            throw new Exception\InvalidArgumentException("Unexpected adjustment mode '$mode'.");
        }
    }

    /**
     * Returns whether the given item should be skipped regarding to the configured filter.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return bool
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
     *
     * @param Model\SaleItemInterface $item
     * @param Model\Amount            $result
     */
    protected function mergeItemsResults(Model\SaleItemInterface $item, Model\Amount $result): void
    {
        // At this points items result are calculated and set.
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
     *
     * @param Model\AdjustmentInterface $adjustment
     * @param string                    $expected
     */
    protected function assertAdjustmentType(Model\AdjustmentInterface $adjustment, string $expected): void
    {
        if ($expected !== $type = $adjustment->getType()) {
            throw new Exception\InvalidArgumentException("Unexpected adjustment type '$type'.");
        }
    }

    /**
     * Calculates the real sale gross base amount.
     *
     * @param Model\SaleInterface $sale
     *
     * @return float
     *
     * @throws Exception\LogicException
     */
    protected function getRealGrossBase(Model\SaleInterface $sale): float
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
     *
     * @param string $key
     *
     * @return Model\Amount|null
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
     *
     * @param string       $key
     * @param Model\Amount $amount
     */
    protected function set(string $key, Model\Amount $amount): void
    {
        $this->cache[$key] = $amount;
    }
}

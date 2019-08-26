<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception;

/**
 * Class AmountCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AmountCalculator implements AmountCalculatorInterface
{
    /**
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * @var CurrencyConverterInterface
     */
    private $converter;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface   $contextProvider
     * @param CurrencyConverterInterface $converter
     */
    public function __construct(ContextProviderInterface $contextProvider, CurrencyConverterInterface $converter)
    {
        $this->contextProvider = $contextProvider;
        $this->converter = $converter;
    }

    /**
     * Returns the currency converter.
     *
     * @return CurrencyConverterInterface
     */
    public function getCurrencyConverter(): CurrencyConverterInterface
    {
        return $this->converter;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultCurrency(): string
    {
        return $this->converter->getDefaultCurrency();
    }

    /**
     * @inheritDoc
     */
    public function calculateSale(Model\SaleInterface $sale, string $currency = null, bool $cache = true): Model\Amount
    {
        $currency = $currency ?? $this->converter->getDefaultCurrency();

        // Don't calculate twice
        if ($cache && ($result = $sale->getFinalResult($currency))) {
            return $result;
        }

        // Items / Gross result
        $gross = $this->calculateSaleItems($sale, $currency, $cache);

        // Final result
        $final = Model\Amount::createFinalFromGross($gross);

        // Discounts
        if ($sale->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
            foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
                $this->calculateSaleDiscount($adjustment, $gross, $final, $currency, $cache);
            }
        }

        // Shipment
        $this->calculateSaleShipment($sale, $final, $currency, $cache);

        // Round/finalize results.
        $gross->round();
        $final->finalize();

        // Store the results
        if ($cache) {
            $sale->setGrossResult($gross);
            $sale->setFinalResult($final);
        }

        return $final;
    }

    /**
     * @inheritDoc
     */
    public function calculateSaleItems(
        Model\SaleInterface $sale,
        string $currency = null,
        bool $cache = true
    ): Model\Amount {
        $currency = $currency ?? $this->converter->getDefaultCurrency();

        if ($cache && ($result = $sale->getGrossResult($currency))) {
            return $result;
        }

        $result = new Model\Amount($currency);

        // Sum public
        foreach ($sale->getItems() as $item) {
            if ($item->isPrivate()) {
                throw new Exception\LogicException("Root sale items can't be private.");
            }

            // Skip compound with only public children
            if (!($item->isCompound() && !$item->hasPrivateChildren())) {
                $result->merge($this->calculateSaleItem($item, null, $currency, $cache));
            }

            $this->mergeItemsResults($item, $result, $cache);
        }

        // Set unit = gross
        $result->copyGrossToUnit();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function calculateSaleItem(
        Model\SaleItemInterface $item,
        float $quantity = null,
        string $currency = null,
        bool $cache = true
    ): Model\Amount {
        // TODO use packaging format on quantities

        if (null !== $quantity) {
            if (0 >= $quantity) {
                throw new Exception\InvalidArgumentException("Specific quantity must be greater than zero.");
            }

            $cache = false; // Never use cache with specific quantity
        }

        $currency = $currency ?? $this->converter->getDefaultCurrency();

        // Don't calculate twice
        if ($cache && ($result = $item->getResult($currency))) {
            return $result;
        }

        $sale = $item->getSale();
        $ati = $sale->isAtiDisplayMode();

        $taxGroup = $item->getTaxGroup();

        // Round unit price only for 'net' calculation
        $unit = $this->converter->convertWithSubject((float)$item->getNetPrice(), $sale, $currency, !$ati);

        // Add private items unit prices
        foreach ($item->getChildren() as $child) {
            $q = $quantity ? $quantity * $child->getQuantity() : null;
            $childResult = $this->calculateSaleItem($child, $q, $currency, $cache);

            if ($child->isPrivate()) {
                if ($taxGroup !== $child->getTaxGroup()) {
                    throw new Exception\LogicException("Private item must have the same tax group as its parent.");
                }
                if ($child->hasAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)) {
                    throw new Exception\LogicException("Private items can't have discount adjustment.");
                }

                $unit += $ati
                    ? $childResult->getUnit() * $child->getQuantity()
                    : Money::round($childResult->getUnit(), $currency) * $child->getQuantity();
            }
        }

        if ($item->getSale()->isSample()) {
            // Sample sale case : zero amounts
            $result = new Model\Amount($currency);
        } elseif ($item->isPrivate()) {
            // Private case : we just need unit amount
            $gross = $unit * $item->getTotalQuantity();
            $result = new Model\Amount($currency, $unit, $gross, 0, $gross);
        } else {
            // Regular case
            $discounts = $taxes = [];
            $discount = $tax = 0;

            // Gross price
            $gross = $unit * (null !== $quantity ? $quantity : $item->getTotalQuantity());

            $parent = $item->getParent();
            $discountAdjustments = $item->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)->toArray();

            // Items without subject inherit discounts from their non-compound parent
            if (empty($discountAdjustments) && !$item->hasSubjectIdentity() && null !== $parent && !$parent->isCompound()) {
                $discountAdjustments = $parent->getAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT)->toArray();
            }

            // Discount amount and result adjustments
            $discountBase = $gross;
            foreach ($discountAdjustments as $data) {
                $adjustment = $this->createPercentAdjustment($data, $discountBase, $currency);
                $discountBase -= $adjustment->getAmount();
                $discount += $adjustment->getAmount();
                $discounts[] = $adjustment;
            }

            // Base
            $base = $ati
                ? round($gross - $discount, 5)
                : Money::round($gross - $discount, $currency);

            // Tax amount and result adjustments
            $taxAdjustments = $item->getAdjustments(Model\AdjustmentTypes::TYPE_TAXATION)->toArray();
            foreach ($taxAdjustments as $data) {
                $adjustment = $this->createPercentAdjustment($data, $base, $currency);

                $tax += $adjustment->getAmount();
                $taxes[] = $adjustment;
            }

            // Total
            $total = Money::round($base + $tax, $currency);

            // Result
            $result = new Model\Amount($currency, $unit, $gross, $discount, $base, $tax, $total, $discounts, $taxes);
        }

        if ($ati) {
            $result->round();
        }

        // Store the result
        if ($cache) {
            $item->setResult($result);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function calculateSaleDiscount(
        Model\SaleAdjustmentInterface $adjustment,
        Model\Amount $gross,
        Model\Amount $final,
        string $currency = null,
        bool $cache = true
    ): Model\Amount {
        $currency = $currency ?? $this->converter->getDefaultCurrency();

        // Don't calculate twice
        if ($cache && ($result = $adjustment->getResult($currency))) {
            return $result;
        }

        $this->assertAdjustmentType($adjustment, Model\AdjustmentTypes::TYPE_DISCOUNT);

        /** @var Model\SaleInterface $sale */
        $sale = $adjustment->getAdjustable();

        // Sample sale case
        if ($sale->isSample()) {
            $result = new Model\Amount($currency);

            if ($cache) {
                $adjustment->setResult($result);
            }

            return $result;
        }
        $base = $gross->getBase();

        $result = new Model\Amount($currency);

        $mode = $adjustment->getMode();
        if (Model\AdjustmentModes::MODE_PERCENT === $mode) {
            $rate = (float)$adjustment->getAmount();

            $result->addUnit(Money::round($base * $rate / 100, $currency));

            foreach ($gross->getTaxAdjustments() as $tax) {
                $amount = Money::round($tax->getAmount() * $rate / 100, $currency);
                $result->addTaxAdjustment(new Model\Adjustment($tax->getName(), $amount, $tax->getRate()));
                $result->addTax($amount);
            }
        } elseif (Model\AdjustmentModes::MODE_FLAT === $mode) {
            $realBase = $this->getRealGrossBase($sale, $currency);

            $amount = $this->converter->convertWithSubject((float)$adjustment->getAmount(), $sale, $currency, false);

            if (1 === Money::compare($realBase, $base, $currency)) {
                $unit = Money::round($amount * $base / $realBase, $currency);
            } else {
                $unit = $amount;
            }
            $result->addUnit($unit);

            foreach ($gross->getTaxAdjustments() as $tax) {
                $amount = Money::round($unit * $tax->getAmount() / $base, $currency);
                $result->addTaxAdjustment(new Model\Adjustment($tax->getName(), $amount, $tax->getRate()));
                $result->addTax($amount);
            }
        } else {
            throw new Exception\InvalidArgumentException("Unexpected adjustment mode '$mode'.");
        }

        $result->addGross($result->getUnit());
        $result->addBase($result->getUnit());
        $result->addTotal($result->getUnit() + $result->getTax());

        if ($cache) {
            $adjustment->setResult($result);
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
        Model\Amount $final,
        string $currency = null,
        bool $cache = true
    ): ?Model\Amount {
        $currency = $currency ?? $this->converter->getDefaultCurrency();

        // Don't calculate twice
        if ($cache && ($result = $sale->getShipmentResult($currency))) {
            return $result;
        }

        // Sample sale case
        if ($sale->isSample()) {
            $result = new Model\Amount($currency);

            if ($cache) {
                $sale->setShipmentResult($result);
            }

            return $result;
        }

        // Abort if shipment cost is lower than or equals zero
        $base = $this->converter->convertWithSubject($sale->getShipmentAmount(), $sale, $currency, false);
        $result = new Model\Amount($currency, $base, $base, 0, $base, 0, $base);

        if (1 === Money::compare($base, 0, $currency)) {
            // Shipment taxation
            foreach ($sale->getAdjustments(Model\AdjustmentTypes::TYPE_TAXATION) as $data) {
                $adjustment = $this->createPercentAdjustment($data, $base, $currency);

                $result->addTaxAdjustment($adjustment);
                $result->addTax($adjustment->getAmount());
                $result->addTotal($adjustment->getAmount());
            }

            // Add to final result
            $final->addBase($result->getBase());
            $final->addTotal($result->getBase());

            foreach ($result->getTaxAdjustments() as $a) {
                $final->addTaxAdjustment($a);
                $final->addTax($a->getAmount());
                $final->addTotal($a->getAmount());
            }
        }

        if ($sale->isAtiDisplayMode()) {
            $result->round();
        }

        // Store shipment result
        if ($cache) {
            $sale->setShipmentResult($result);
        }

        return $result;
    }

    /**
     * Creates a new result adjustment.
     *
     * @param Model\AdjustmentInterface $data
     * @param float                     $base
     * @param string                    $currency
     *
     * @return Model\Adjustment
     */
    protected function createPercentAdjustment(
        Model\AdjustmentInterface $data,
        float $base,
        string $currency
    ): Model\Adjustment {
        $this->assertAdjustmentMode($data, Model\AdjustmentModes::MODE_PERCENT);

        $rate = (float)$data->getAmount();

        if ($data->getType() === Model\AdjustmentTypes::TYPE_TAXATION) {
            // Calculate taxation as ATI - NET
            $amount = Money::round($base * (1 + $rate / 100), $currency) - Money::round($base, $currency);
        } else {
            $amount = Money::round($base * $rate / 100, $currency);
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
     * Merges the public children results recursively into the given result.
     *
     * @param Model\SaleItemInterface $item
     * @param Model\Amount            $result
     * @param bool                    $cache
     */
    protected function mergeItemsResults(Model\SaleItemInterface $item, Model\Amount $result, bool $cache): void
    {
        // At this points items result are calculated and set.
        foreach ($item->getChildren() as $child) {
            if ($child->isPrivate()) {
                continue;
            }

            // Skip compound with only public children
            if (!($child->isCompound() && !$child->hasPrivateChildren())) {
                $result->merge($this->calculateSaleItem($child, null, $result->getCurrency(), $cache));
            }

            if ($child->hasChildren()) {
                $this->mergeItemsResults($child, $result, $cache);
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
     * @param string              $currency
     *
     * @return float
     *
     * @throws Exception\LogicException
     */
    protected function getRealGrossBase(Model\SaleInterface $sale, string $currency): float
    {
        // Calculate real gross base
        return $this->calculateSaleItems($sale, $currency, false)->getBase();
    }
}

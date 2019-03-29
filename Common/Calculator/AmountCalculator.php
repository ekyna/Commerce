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
     * @var bool
     */
    private $cache = true;


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
     * @inheritdoc
     */
    public function setCache(bool $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function calculateSale(Model\SaleInterface $sale): Amount
    {
        $this->setCache(true);

        // Don't calculate twice
        if (null !== $result = $sale->getFinalResult()) {
            return $result;
        }

        // Items / Gross result
        $gross = $this->calculateSaleItems($sale);

        // Final result
        $final = Amount::createFinalFromGross($gross);

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

        // Store the results
        $sale->setGrossResult($gross);
        $sale->setFinalResult($final);

        return $final;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleItem(Model\SaleItemInterface $item, float $quantity = null): Amount
    {
        // TODO use packaging format on quantities

        if (null !== $quantity && 0 == $quantity) {
            throw new Exception\InvalidArgumentException("Custom quantity must be greater than zero.");
        }

        // Don't calculate twice
        if ($this->cache && null !== $result = $item->getResult()) {
            return $result;
        }

        $sale = $item->getSale();
        $currency = $sale->getCurrency()->getCode();
        $ati = $sale->isAtiDisplayMode();

        $taxGroup = $item->getTaxGroup();

        // Round unit price only for 'net' calculation
        $unit = $this->convert($sale, (float)$item->getNetPrice(), $currency, !$ati);

        // Add private items unit prices
        foreach ($item->getChildren() as $child) {
            $childResult = $this->calculateSaleItem($child,
                null !== $quantity ? $quantity * $child->getQuantity() : null);

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

        if ($item->isPrivate()) {
            // Private case : we just need unit amount
            $gross = $unit * $item->getTotalQuantity();
            $result = new Amount($currency, $unit, $gross, 0, $gross);
        } elseif ($item->getSale()->isSample()) {
            // Sample sale case : zero amounts
            $result = new Amount($currency);
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
            $result = new Amount($currency, $unit, $gross, $discount, $base, $tax, $total, $discounts, $taxes);
        }

        if ($ati) {
            $result->round();
        }

        // Store the result
        $item->setResult($result);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleDiscount(
        Model\SaleAdjustmentInterface $adjustment,
        Amount $gross,
        Amount $final
    ): Amount {
        // Don't calculate twice
        if ($this->cache && null !== $result = $adjustment->getResult()) {
            return $result;
        }

        $this->assertAdjustmentType($adjustment, Model\AdjustmentTypes::TYPE_DISCOUNT);

        /** @var Model\SaleInterface $sale */
        $sale = $adjustment->getAdjustable();
        $currency = $sale->getCurrency()->getCode();

        // Sample sale case
        if ($sale->isSample()) {
            $result = new Amount($currency);
            $adjustment->setResult($result);

            return $result;
        }
        $base = $gross->getBase();

        $result = new Amount($currency);

        $mode = $adjustment->getMode();
        if (Model\AdjustmentModes::MODE_PERCENT === $mode) {
            $rate = (float)$adjustment->getAmount();

            $result->addUnit(Money::round($base * $rate / 100, $currency));

            foreach ($gross->getTaxAdjustments() as $tax) {
                $amount = Money::round($tax->getAmount() * $rate / 100, $currency);
                $result->addTaxAdjustment(new Adjustment($tax->getName(), $amount, $tax->getRate()));
                $result->addTax($amount);
            }
        } elseif (Model\AdjustmentModes::MODE_FLAT === $mode) {
            $realBase = $this->getRealGrossBase($sale);
            $amount = $this->convert($sale, (float)$adjustment->getAmount(), $currency, false);
            if (1 === Money::compare($realBase, $base, $currency)) {
                $unit = Money::round($amount * $base / $realBase, $currency);
            } else {
                $unit = $amount;
            }
            $result->addUnit($unit);

            foreach ($gross->getTaxAdjustments() as $tax) {
                $amount = Money::round($unit * $tax->getAmount() / $base, $currency);
                $result->addTaxAdjustment(new Adjustment($tax->getName(), $amount, $tax->getRate()));
                $result->addTax($amount);
            }
        } else {
            throw new Exception\InvalidArgumentException("Unexpected adjustment mode '$mode'.");
        }

        $result->addGross($result->getUnit());
        $result->addBase($result->getUnit());
        $result->addTotal($result->getUnit() + $result->getTax());

        $adjustment->setResult($result);

        // Add to final result
        $final->addDiscount($result->getBase());
        $final->addBase(-$result->getBase());
        $final->addTotal(-$result->getBase());
        $final->addDiscountAdjustment(new Adjustment(
            (string)$adjustment->getDesignation(),
            $result->getBase(),
            $adjustment->getMode() === Model\AdjustmentModes::MODE_PERCENT ? (float)$adjustment->getAmount() : 0
        ));

        foreach ($result->getTaxAdjustments() as $a) {
            $final->addTaxAdjustment(new Adjustment($a->getName(), -$a->getAmount(), $a->getRate()));
            $final->addTax(-$a->getAmount());
            $final->addTotal(-$a->getAmount());
        }

        if ($sale->isAtiDisplayMode()) {
            $result->round();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleShipment(Model\SaleInterface $sale, Amount $final): ?Amount
    {
        // Don't calculate twice
        if ($this->cache && null !== $result = $sale->getShipmentResult()) {
            return $result;
        }

        $currency = $sale->getCurrency()->getCode();

        // Sample sale case
        if ($sale->isSample()) {
            $result = new Amount($currency);
            $sale->setShipmentResult($result);

            return $result;
        }

        // Abort if shipment cost is lower than or equals zero
        $base = $this->convert($sale, (float)$sale->getShipmentAmount(), $currency, false);
        $result = new Amount($currency, $base, $base, 0, $base, 0, $base);

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
        $sale->setShipmentResult($result);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleItems(Model\SaleInterface $sale): Amount
    {
        if ($this->cache && null !== $result = $sale->getGrossResult()) {
            return $result;
        }

        $result = new Amount($sale->getCurrency()->getCode());

        // Sum public
        foreach ($sale->getItems() as $item) {
            if ($item->isPrivate()) {
                throw new Exception\LogicException("Sale's root items can't be private.");
            }

            $this->calculateSaleItem($item);

            // Skip compound with only public children
            if (!($item->isCompound() && !$item->hasPrivateChildren())) {
                $result->merge($item->getResult());
            }

            $this->mergeItemsResults($item, $result);
        }

        // Set unit = gross
        $result->copyGrossToUnit();

        return $result;
    }

    /**
     * Merges the public children results recursively into the given result.
     *
     * @param Model\SaleItemInterface $item
     * @param Amount                  $result
     */
    protected function mergeItemsResults(Model\SaleItemInterface $item, Amount $result): void
    {
        // At this points items result are calculated and set.
        foreach ($item->getChildren() as $child) {
            if ($child->isPrivate()) {
                continue;
            }

            // Skip compound with only public children
            if (!($child->isCompound() && !$child->hasPrivateChildren())) {
                $result->merge($child->getResult());
            }

            if ($child->hasChildren()) {
                $this->mergeItemsResults($child, $result);
            }
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
        // Store previous cache mode
        $cache = $this->cache;

        // Disable cache
        $this->cache = false;

        // Calculate real gross base
        $base = $this->calculateSaleItems($sale)->getBase();

        // Restore cache mode
        $this->cache = $cache;

        return $base;
    }

    /**
     * Creates a new result adjustment.
     *
     * @param Model\AdjustmentInterface $data
     * @param float                     $base
     * @param string                    $currency
     *
     * @return Adjustment
     */
    protected function createPercentAdjustment(
        Model\AdjustmentInterface $data,
        float $base,
        string $currency
    ): Adjustment {
        $this->assertAdjustmentMode($data, Model\AdjustmentModes::MODE_PERCENT);

        $rate = (float)$data->getAmount();

        if ($data->getType() === Model\AdjustmentTypes::TYPE_TAXATION) {
            // Calculate taxation as ATI - NET
            $amount = Money::round($base * (1 + $rate / 100), $currency) - Money::round($base, $currency);
        } else {
            $amount = Money::round($base * $rate / 100, $currency);
        }

        return new Adjustment((string)$data->getDesignation(), $amount, $rate);
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
     * Converts the given amount.
     *
     * @param Model\SaleInterface $sale
     * @param float               $amount
     * @param string              $currency
     * @param bool                $round
     *
     * @return float
     * @throws \Exception
     */
    protected function convert(Model\SaleInterface $sale, float $amount, string $currency, bool $round)
    {
        if ($currency === $this->converter->getDefaultCurrency()) {
            return $round ? Money::round($amount, $currency) : $amount;
        }

        if (null !== $rate = $sale->getExchangeRate()) {
            return $this
                ->converter
                ->convertWithRate($amount, $rate, $currency, $round);
        }

        $date = $this->contextProvider->getContext($sale)->getDate();

        return $this
            ->converter
            ->convert($amount, $this->converter->getDefaultCurrency(), $currency, $date, $round);
    }
}

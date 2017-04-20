<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class Amount
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Amount
{
    private string $currency;
    private Decimal $unit;
    private Decimal $gross;
    private Decimal $discount;
    private Decimal $base;
    private Decimal $tax;
    private Decimal $total;
    /** @var array<Adjustment> */
    private array $discounts;
    /** @var array<Adjustment> */
    private array $taxes;


    /**
     * @param array<Adjustment> $discounts
     * @param array<Adjustment> $taxes
     */
    public function __construct(
        string $currency,
        Decimal $unit = null,
        Decimal $gross = null,
        Decimal $discount = null,
        Decimal $base = null,
        Decimal $tax = null,
        Decimal $total = null,
        array $discounts = [],
        array $taxes = []
    ) {
        $this->currency = $currency;
        $this->unit = $unit ?: new Decimal(0);
        $this->gross = $gross ?: new Decimal(0);
        $this->discount = $discount ?: new Decimal(0);
        $this->base = $base ?: new Decimal(0);
        $this->tax = $tax ?: new Decimal(0);
        $this->total = $total ?: new Decimal(0);

        $this->taxes = $taxes;
        $this->discounts = $discounts;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getUnit(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->unit) : $this->unit;
    }

    public function addUnit(Decimal $amount): void
    {
        $this->unit += $amount;
    }

    public function getGross(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->gross) : $this->gross;
    }

    public function addGross(Decimal $amount): void
    {
        $this->gross += $amount;
    }

    /**
     * @return Adjustment[]
     */
    public function getDiscountAdjustments(): array
    {
        return $this->discounts;
    }

    public function addDiscountAdjustment(Adjustment $discount): void
    {
        foreach ($this->discounts as $d) {
            if ($d->isSameAs($discount)) {
                $d->addAmount($discount->getAmount());

                return;
            }
        }

        $this->discounts[] = clone $discount;
    }

    public function getDiscount(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->discount) : $this->discount;
    }

    public function addDiscount(Decimal $amount): void
    {
        $this->discount += $amount;
    }

    public function getBase(bool $ati = false): Decimal
    {
        return $ati ? $this->ati($this->base) : $this->base;
    }

    public function addBase(Decimal $amount): void
    {
        $this->base += $amount;
    }

    /**
     * @return Adjustment[]
     */
    public function getTaxAdjustments(): array
    {
        return $this->taxes;
    }

    public function addTaxAdjustment(Adjustment $tax): void
    {
        foreach ($this->taxes as $t) {
            if ($t->isSameAs($tax)) {
                $t->addAmount($tax->getAmount());

                return;
            }
        }

        $this->taxes[] = clone $tax;
    }

    public function getTax(): Decimal
    {
        return $this->tax;
    }

    public function addTax(Decimal $amount): void
    {
        $this->tax += $amount;
    }

    public function getTotal(): Decimal
    {
        return $this->total;
    }

    public function addTotal(Decimal $amount): void
    {
        $this->total += $amount;
    }

    /**
     * Adds the taxes to the given amount.
     */
    private function ati(Decimal $amount): Decimal
    {
        $result = $amount;

        foreach ($this->taxes as $tax) {
            $result += $amount * $tax->getRate() / 100;
        }

        return Money::round($result, $this->currency);
    }

    /**
     * Merges the given amounts (by sum).
     */
    public function merge(Amount ...$amounts): void
    {
        foreach ($amounts as $amount) {
            if ($amount->getCurrency() !== $this->currency) {
                throw new RuntimeException('Currencies miss match.');
            }

            $this->unit += $amount->getUnit();
            $this->gross += $amount->getGross();
            $this->discount += $amount->getDiscount();
            $this->base += $amount->getBase();
            $this->tax += $amount->getTax();
            $this->total += $amount->getTotal();

            foreach ($amount->getDiscountAdjustments() as $a) {
                $this->addDiscountAdjustment($a);
            }

            foreach ($amount->getTaxAdjustments() as $a) {
                $this->addTaxAdjustment($a);
            }
        }
    }

    /**
     * Overrides the unit amount with the gross amount.
     */
    public function copyGrossToUnit(): void
    {
        $this->unit = $this->gross;
    }

    /**
     * Rounds the tax adjustments amounts.
     */
    public function finalize(): void
    {
        $this->round();

        $old = $this->taxes;

        // Sort by amount
        usort($old, function (Adjustment $a, Adjustment $b): int {
            return $a->getAmount()->compareTo($b->getAmount());
        });

        $new = [];
        $total = 0;
        foreach ($old as $tax) {
            $amount = Money::round($tax->getAmount(), $this->currency);

            // Fix overflow
            if ($total + $amount > $this->tax) {
                $amount = $this->tax - $total;
            }
            $total += $amount;

            $new[] = new Adjustment($tax->getName(), $amount, $tax->getRate());
        }

        // Sort by rate
        usort($new, function (Adjustment $a, Adjustment $b): int {
            return $a->getRate() > $b->getRate() ? 1 : -1;
        });

        $this->taxes = $new;
    }

    /**
     * Rounds the amounts.
     */
    public function round(): void
    {
        $this->unit = Money::round($this->unit, $this->currency);
        $this->gross = Money::round($this->gross, $this->currency);
        $this->discount = Money::round($this->discount, $this->currency);
        $this->base = Money::round($this->base, $this->currency);
        $this->total = Money::round($this->total, $this->currency);
        $this->tax = Money::round($this->total - $this->base, $this->currency);
    }

    /**
     * Creates the final result from the given gross result.
     */
    public static function createFinalFromGross(Amount $gross): Amount
    {
        $final = new Amount(
            $gross->getCurrency(),
            $gross->getBase(),
            $gross->getBase(),
            new Decimal(0),
            $gross->getBase(),
            $gross->getTax(),
            $gross->getTotal()
        );
        foreach ($gross->getTaxAdjustments() as $t) {
            $final->addTaxAdjustment($t);
        }

        return $final;
    }
}

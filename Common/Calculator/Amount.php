<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Util\Money;

/**
 * Class Amount
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Amount
{
    /**
     * @var float
     */
    private $unit;

    /**
     * @var float
     */
    private $gross;

    /**
     * @var float
     */
    private $discount;

    /**
     * @var float
     */
    private $base;

    /**
     * @var float
     */
    private $tax;

    /**
     * @var float
     */
    private $total;

    /**
     * @var Adjustment[]
     */
    private $discounts;

    /**
     * @var Adjustment[]
     */
    private $taxes;


    /**
     * Constructor.
     *
     * @param float        $unit
     * @param float        $gross
     * @param float        $discount
     * @param float        $base
     * @param float        $tax
     * @param float        $total
     * @param Adjustment[] $discounts
     * @param Adjustment[] $taxes
     */
    public function __construct(
        float $unit = .0,
        float $gross = .0,
        float $discount = .0,
        float $base = .0,
        float $tax = .0,
        float $total = .0,
        array $discounts = [],
        array $taxes = []
    ) {
        $this->unit = $unit;
        $this->gross = $gross;
        $this->discount = $discount;
        $this->base = $base;
        $this->tax = $tax;
        $this->total = $total;

        $this->taxes = $taxes;
        $this->discounts = $discounts;
    }

    /**
     * Returns the unit.
     *
     * @return float
     */
    public function getUnit(): float
    {
        return $this->unit;
    }

    /**
     * Adds the unit amount.
     *
     * @param float $amount
     */
    public function addUnit(float $amount): void
    {
        $this->unit += $amount;
    }

    /**
     * Returns the gross.
     *
     * @return float
     */
    public function getGross(): float
    {
        return $this->gross;
    }

    /**
     * Adds the gross amount.
     *
     * @param float $amount
     */
    public function addGross(float $amount): void
    {
        $this->gross += $amount;
    }

    /**
     * Returns the discount adjustments.
     *
     * @return Adjustment[]
     */
    public function getDiscountAdjustments(): array
    {
        return $this->discounts;
    }

    /**
     * Adds the discount adjustment.
     *
     * @param Adjustment $discount
     */
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

    /**
     * Returns the discount.
     *
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * Adds the discount amount.
     *
     * @param float $amount
     */
    public function addDiscount(float $amount): void
    {
        $this->discount += $amount;
    }

    /**
     * Returns the base.
     *
     * @return float
     */
    public function getBase(): float
    {
        return $this->base;
    }

    /**
     * Adds the base amount.
     *
     * @param float $amount
     */
    public function addBase(float $amount): void
    {
        $this->base += $amount;
    }

    /**
     * Returns the tax adjustments.
     *
     * @return Adjustment[]
     */
    public function getTaxAdjustments(): array
    {
        return $this->taxes;
    }

    /**
     * Adds the tax adjustment.
     *
     * @param Adjustment $tax
     */
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

    /**
     * Returns the tax.
     *
     * @return float
     */
    public function getTax(): float
    {
        return $this->tax;
    }

    /**
     * Adds the tax amount.
     *
     * @param float $amount
     */
    public function addTax(float $amount): void
    {
        $this->tax += $amount;
    }

    /**
     * Returns the total.
     *
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * Adds the total amount.
     *
     * @param float $amount
     */
    public function addTotal(float $amount): void
    {
        $this->total += $amount;
    }

    /**
     * Merges the given amounts (by sum).
     *
     * @param Amount[] $amounts
     */
    public function merge(Amount ...$amounts): void
    {
        foreach ($amounts as $amount) {
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
     * Rounds the taxes regarding to currency.
     *
     * @param string $currency
     */
    public function roundTax(string $currency): void
    {
        $this->tax = Money::round($this->tax, $currency);
    }

    /**
     * Rounds the tax adjustments amounts.
     *
     * @param string $currency
     */
    public function roundTaxAdjustments(string $currency): void
    {
        $old = $this->taxes;

        // Sort by amount
        usort($old, function(Adjustment $a, Adjustment $b):int {
            if ($a->getAmount() == $b->getAmount()) {
                return 0;
            }

            return $a->getAmount() > $b->getAmount() ? 1 : -1;
        });

        $new = [];
        $total = 0;
        foreach ($old as $tax) {
            $amount = Money::round($tax->getAmount(), $currency);

            // Fix overflow
            if ($total + $amount > $this->tax) {
                $amount = $this->tax - $total;
            }
            $total += $amount;

            $new[] = new Adjustment($tax->getName(), $amount, $tax->getRate());
        }

        // Sort by rate
        usort($new, function(Adjustment $a, Adjustment $b):int {
            return $a->getRate() > $b->getRate() ? 1 : -1;
        });

        $this->taxes = $new;
    }

    /**
     * Creates the final result from the given gross result.
     *
     * @param Amount $gross
     *
     * @return Amount
     */
    public static function createFinalFromGross(Amount $gross): Amount
    {
        $final = new Amount(
            $gross->getBase(),
            $gross->getBase(),
            0,
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

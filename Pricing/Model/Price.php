<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\AdjustmentDataInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class Price
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Price
{
    private Decimal $base;
    private string $currency;
    private string $mode;

    /** @var AdjustmentDataInterface[] */
    private array $discounts;

    /** @var AdjustmentDataInterface[] */
    private array $taxes;


    public function __construct(Decimal $amount, string $currency, string $mode)
    {
        $this->base = $amount;
        $this->currency = $currency;
        $this->mode = $mode;

        $this->discounts = [];
        $this->taxes = [];
    }

    /**
     * Returns the amount.
     */
    public function getBase(): Decimal
    {
        return $this->base;
    }

    /**
     * Returns the currency.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Returns the mode.
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Returns whether this price has taxes.
     */
    public function hasTaxes(): bool
    {
        return !empty($this->taxes);
    }

    /**
     * Adds the taxation adjustment.
     */
    public function addTax(AdjustmentDataInterface $tax): void
    {
        $this->taxes[] = $tax;
    }

    /**
     * Returns whether this price has discounts.
     */
    public function hasDiscounts(): bool
    {
        return !empty($this->discounts);
    }

    /**
     * Adds the discount adjustment.
     */
    public function addDiscount(AdjustmentDataInterface $discount): void
    {
        $this->discounts[] = $discount;
    }

    /**
     * Returns the total.
     *
     * @param bool $discounted Whether to return the discounted price.
     */
    public function getTotal(bool $discounted = true): Decimal
    {
        $base = $this->base;

        if ($discounted && $this->hasDiscounts()) {
            foreach ($this->discounts as $discount) {
                $base -= $this->calculateAdjustment($discount, $base);
            }
        }

        $total = $base;

        if (!empty($this->taxes) && $this->mode === VatDisplayModes::MODE_ATI) {
            foreach ($this->taxes as $tax) {
                $total += $this->calculateAdjustment($tax, $base);
            }
        }

        return $total;
    }

    /**
     * Calculates the adjustment amount.
     */
    private function calculateAdjustment(AdjustmentDataInterface $adjustment, Decimal $base): Decimal
    {
        if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
            return Money::round($base * $adjustment->getAmount() / 100, $this->currency);
        }

        if ($adjustment->getMode() === AdjustmentModes::MODE_FLAT) {
            return $adjustment->getAmount();
        }

        throw new InvalidArgumentException('Unexpected adjustment mode.');
    }
}

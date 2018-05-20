<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

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
    /**
     * @var float
     */
    private $base;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var AdjustmentDataInterface[]
     */
    private $discounts;

    /**
     * @var AdjustmentDataInterface[]
     */
    private $taxes;


    /**
     * Constructor.
     *
     * @param float  $amount
     * @param string $currency
     * @param string $mode
     */
    public function __construct(float $amount, string $currency, string $mode)
    {
        $this->base = $amount;
        $this->currency = $currency;
        $this->mode = $mode;

        $this->discounts = [];
        $this->taxes = [];
    }

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Returns the currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Returns the mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Returns whether this price has taxes.
     *
     * @return bool
     */
    public function hasTaxes()
    {
        return !empty($this->taxes);
    }

    /**
     * Adds the taxation adjustment.
     *
     * @param AdjustmentDataInterface $tax
     */
    public function addTax(AdjustmentDataInterface $tax)
    {
        $this->taxes[] = $tax;
    }

    /**
     * Returns whether this price has discounts.
     *
     * @return bool
     */
    public function hasDiscounts()
    {
        return !empty($this->discounts);
    }

    /**
     * Adds the discount adjustment.
     *
     * @param AdjustmentDataInterface $discount
     */
    public function addDiscount(AdjustmentDataInterface $discount)
    {
        $this->discounts[] = $discount;
    }

    /**
     * Returns the total.
     *
     * @param bool $discounted Whether to return the discounted price.
     *
     * @return float
     */
    public function getTotal($discounted = true)
    {
        $total = $this->base;

        if ($discounted && $this->hasDiscounts()) {
            foreach ($this->discounts as $discount) {
                $total -= $this->calculateAdjustment($discount, $this->base);
            }
        }

        $base = $total;

        if (!empty($this->taxes) && $this->mode === VatDisplayModes::MODE_ATI) {
            foreach ($this->taxes as $tax) {
                $total += $this->calculateAdjustment($tax, $base);
            }
        }

        return $total;
    }

    /**
     * Calculates the adjustment amount.
     *
     * @param AdjustmentDataInterface $adjustment
     * @param                         $base
     *
     * @return float
     */
    private function calculateAdjustment(AdjustmentDataInterface $adjustment, $base)
    {
        if ($adjustment->getMode() === AdjustmentModes::MODE_PERCENT) {
            return Money::round($base * $adjustment->getAmount() / 100, $this->currency);
        }

        if ($adjustment->getMode() === AdjustmentModes::MODE_FLAT) {
            return $adjustment->getAmount();
        }

        throw new InvalidArgumentException("Unexpected adjustment mode.");
    }
}

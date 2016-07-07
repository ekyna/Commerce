<?php

namespace Ekyna\Component\Commerce\Pricing\Total;

/**
 * Interface TaxAmountInterface
 * @package Ekyna\Component\Commerce\Pricing\Total
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AmountInterface
{
    /**
     * Returns the base amount.
     *
     * @return float
     */
    public function getBase();

    /**
     * Adds to the base.
     *
     * @param float $base
     */
    public function addBase($base);

    /**
     * Removes from the base.
     *
     * @param float $base
     */
    public function removeBase($base);

    /**
     * Returns the tax rate.
     *
     * @return float
     */
    public function getTaxRate();

    /**
     * Returns the tax name.
     *
     * @return string
     */
    public function getTaxName();

    /**
     * Returns whether or not this tax amount and the given tax amount
     * have the same tax name and tax rate.
     *
     * @param AmountInterface $amount
     *
     * @return bool
     */
    public function equals(AmountInterface $amount);

    /**
     * Merges the amount.
     *
     * @param AmountInterface $amount
     */
    public function merge(AmountInterface $amount);

    /**
     * Applies the quantity.
     *
     * @param float $quantity
     */
    public function multiply($quantity);

    /**
     * Returns the tax total.
     *
     * @return float
     */
    //public function getTaxTotal();

    /**
     * Returns the total (tax included).
     *
     * @return float
     */
    //public function getTotal();
}

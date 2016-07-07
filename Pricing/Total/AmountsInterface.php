<?php

namespace Ekyna\Component\Commerce\Pricing\Total;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface TotalInterface
 * @package Ekyna\Component\Commerce\Pricing\Total
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AmountsInterface
{
    /**
     * Clears the total.
     */
    public function clear();

    /**
     * Returns the tax amounts.
     *
     * @return ArrayCollection|AmountInterface[]
     */
    public function all();

    /**
     * Returns whether or not the tax amount collection has the tax amount.
     *
     * @param AmountInterface $amount
     *
     * @return bool
     */
    public function has(AmountInterface $amount);

    /**
     * Adds the tax amount.
     *
     * @param AmountInterface $amount
     *
     * @return $this|AmountsInterface
     */
    public function add(AmountInterface $amount);

    /**
     * Removes the tax amount.
     *
     * @param AmountInterface $amount
     *
     * @return $this|AmountsInterface
     */
    public function remove(AmountInterface $amount);

    /**
     * Merge the amount(s).
     *
     * @param AmountInterface|AmountsInterface $amounts
     *
     * @return $this|AmountsInterface
     * @throws \Exception
     */
    public function merge($amounts);

    /**
     * Applies the quantity.
     *
     * @param float $quantity
     */
    public function multiply($quantity);
}

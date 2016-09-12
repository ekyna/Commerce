<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;

/**
 * Interface CartItemAdjustmentInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartItemAdjustmentInterface extends AdjustmentInterface
{
    /**
     * Returns the cart item.
     *
     * @return CartItemInterface
     */
    public function getItem();

    /**
     * Sets the cart item.
     *
     * @param CartItemInterface $item
     * @return $this|CartAdjustmentInterface
     */
    public function setItem(CartItemInterface $item = null);
}

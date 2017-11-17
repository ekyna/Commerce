<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;

/**
 * Interface CartAdjustmentInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartAdjustmentInterface extends SaleAdjustmentInterface
{
    /**
     * Returns the cart.
     *
     * @return CartInterface
     */
    public function getCart();

    /**
     * Sets the cart.
     *
     * @param CartInterface $cart
     * @return $this|CartAdjustmentInterface
     */
    public function setCart(CartInterface $cart = null);
}

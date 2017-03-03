<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface CartItemInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartItemInterface extends SaleItemInterface
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
     *
     * @return $this|CartItemInterface
     */
    public function setCart(CartInterface $cart = null);
}

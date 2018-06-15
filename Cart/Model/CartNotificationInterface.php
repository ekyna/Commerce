<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;

/**
 * Interface CartNotificationInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartNotificationInterface extends SaleNotificationInterface
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
     * @param CartInterface|null $cart
     *
     * @return $this|CartNotificationInterface
     */
    public function setCart(CartInterface $cart = null);
}

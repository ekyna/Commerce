<?php

namespace Ekyna\Component\Commerce\Cart\Provider;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;

/**
 * Interface CartProviderInterface
 * @package Ekyna\Component\Commerce\Cart\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartProviderInterface
{
    /**
     * Returns whether a cart is available.
     *
     * @return bool
     */
    public function hasCart();

    /**
     * Returns the cart.
     *
     * @return CartInterface|null
     */
    public function getCart();

    /**
     * Creates and returns the cart.
     *
     * @return CartInterface
     */
    public function createCart();

    /**
     * Clear the cart.
     *
     * @return CartProviderInterface
     */
    public function clearCart();

    /**
     * Saves the cart.
     *
     * @return CartProviderInterface
     */
    public function saveCart();
}

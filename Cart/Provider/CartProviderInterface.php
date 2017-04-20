<?php

declare(strict_types=1);

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
    public function hasCart(): bool;

    /**
     * Returns the cart.
     *
     * @param bool $create Whether to create the cart if none is available.
     *
     * @return CartInterface|null
     */
    public function getCart(bool $create = false): ?CartInterface;

    /**
     * Creates and returns the cart.
     *
     * @return CartInterface
     */
    public function createCart(): CartInterface;

    /**
     * Clear the cart.
     *
     * @return CartProviderInterface
     */
    public function clearCart(): CartProviderInterface;

    /**
     * Clears the cart information (customer/addresses).
     *
     * @return CartProviderInterface
     */
    public function clearInformation(): CartProviderInterface;

    /**
     * Updates the cart customer group and currency.
     *
     * @return CartProviderInterface
     */
    public function updateCustomerGroupAndCurrency(): CartProviderInterface;

    /**
     * Saves the cart.
     *
     * @return CartProviderInterface
     */
    public function saveCart(): CartProviderInterface;
}

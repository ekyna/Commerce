<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface CartPaymentInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartPaymentInterface extends PaymentInterface
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
     * @return $this|CartPaymentInterface
     */
    public function setCart(CartInterface $cart = null);
}

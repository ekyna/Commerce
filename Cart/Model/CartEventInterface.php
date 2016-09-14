<?php

namespace Ekyna\Component\Commerce\Cart\Model;

/**
 * Interface CartEventInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartEventInterface
{
    /**
     * Returns the cart.
     *
     * @return CartInterface
     */
    public function getCart();
}

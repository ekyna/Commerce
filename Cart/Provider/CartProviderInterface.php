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
     * Returns the cart.
     *
     * @return CartInterface
     */
    public function getCart();
}

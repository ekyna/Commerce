<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;

/**
 * Interface CartAttachmentInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartAttachmentInterface extends SaleAttachmentInterface
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
     * @return $this|CartAttachmentInterface
     */
    public function setCart(CartInterface $cart = null);
}

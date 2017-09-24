<?php

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAddressInterface;

/**
 * Interface CartAddressInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartAddressInterface extends SaleAddressInterface
{
    /**
     * Returns the cart this address is the invoice one.
     *
     * @return CartInterface|null
     */
    public function getInvoiceCart();

    /**
     * Sets the cart this address is the invoice one.
     *
     * @param CartInterface $cart
     *
     * @return $this|CartAddressInterface
     */
    public function setInvoiceCart(CartInterface $cart = null);

    /**
     * Returns the cart this address is the delivery one.
     *
     * @return CartInterface|null
     */
    public function getDeliveryCart();

    /**
     * Sets the cart this address is the delivery one.
     *
     * @param CartInterface $cart
     *
     * @return $this|CartAddressInterface
     */
    public function setDeliveryCart(CartInterface $cart = null);

    /**
     * Returns the related cart.
     *
     * @return CartInterface|null
     */
    public function getCart();
}

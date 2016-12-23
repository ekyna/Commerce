<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model;
use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;

/**
 * Class CartAddress
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddress extends AbstractAddress implements Model\CartAddressInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\CartInterface
     *
     * @todo remove ?
     */
    protected $invoiceCart;

    /**
     * @var Model\CartInterface
     *
     * @todo remove ?
     */
    protected $deliveryCart;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceCart()
    {
        return $this->invoiceCart;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceCart(Model\CartInterface $cart = null)
    {
        if ($cart != $this->invoiceCart) {
            $this->invoiceCart = $cart;
            $cart->setInvoiceAddress($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryCart()
    {
        return $this->deliveryCart;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryCart(Model\CartInterface $cart = null)
    {
        if ($cart != $this->deliveryCart) {
            $this->deliveryCart = $cart;
            $cart->setDeliveryAddress($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCart()
    {
        if (null !== $this->invoiceCart) {
            return $this->invoiceCart;
        } elseif (null !== $this->deliveryCart) {
            return $this->deliveryCart;
        }

        return null;
    }
}

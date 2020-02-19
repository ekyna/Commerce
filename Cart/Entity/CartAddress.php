<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model;
use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAddress;

/**
 * Class CartAddress
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddress extends AbstractSaleAddress implements Model\CartAddressInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\CartInterface
     */
    protected $invoiceCart;

    /**
     * @var Model\CartInterface
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
        if ($cart !== $this->invoiceCart) {
            if ($previous = $this->invoiceCart) {
                $this->invoiceCart = null;
                $previous->setInvoiceAddress(null);
            }

            if ($this->invoiceCart = $cart) {
                $this->invoiceCart->setInvoiceAddress($this);
            }
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
        if ($cart !== $this->deliveryCart) {
            if ($previous = $this->deliveryCart) {
                $this->deliveryCart = null;
                $previous->setDeliveryAddress(null);
            }

            if ($this->deliveryCart = $cart) {
                $this->deliveryCart->setDeliveryAddress($this);
            }
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

    /**
     * @inheritDoc
     */
    public function getSale()
    {
        return $this->getCart();
    }
}

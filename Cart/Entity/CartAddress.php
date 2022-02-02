<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model;
use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAddress;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Class CartAddress
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddress extends AbstractSaleAddress implements Model\CartAddressInterface
{
    protected ?Model\CartInterface $invoiceCart  = null;
    protected ?Model\CartInterface $deliveryCart = null;

    public function getInvoiceCart(): ?Model\CartInterface
    {
        return $this->invoiceCart;
    }

    public function setInvoiceCart(?Model\CartInterface $cart): Model\CartAddressInterface
    {
        if ($cart === $this->invoiceCart) {
            return $this;
        }

        if ($previous = $this->invoiceCart) {
            $this->invoiceCart = null;
            $previous->setInvoiceAddress(null);
        }

        if ($this->invoiceCart = $cart) {
            $this->invoiceCart->setInvoiceAddress($this);
        }

        return $this;
    }

    public function getDeliveryCart(): ?Model\CartInterface
    {
        return $this->deliveryCart;
    }

    public function setDeliveryCart(?Model\CartInterface $cart): Model\CartAddressInterface
    {
        if ($cart === $this->deliveryCart) {
            return $this;
        }

        if ($previous = $this->deliveryCart) {
            $this->deliveryCart = null;
            $previous->setDeliveryAddress(null);
        }

        if ($this->deliveryCart = $cart) {
            $this->deliveryCart->setDeliveryAddress($this);
        }

        return $this;
    }

    public function getCart(): ?Model\CartInterface
    {
        return $this->invoiceCart ?: $this->deliveryCart;
    }

    public function getSale(): ?SaleInterface
    {
        return $this->getCart();
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Payment\Entity\AbstractPayment;

/**
 * Class CartPayment
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartPayment extends AbstractPayment implements Model\CartPaymentInterface
{
    protected ?Model\CartInterface $cart = null;


    public function getSale(): ?SaleInterface
    {
        return $this->getCart();
    }

    public function getCart(): ?Model\CartInterface
    {
        return $this->cart;
    }

    public function setCart(?Model\CartInterface $cart): Model\CartPaymentInterface
    {
        if ($cart === $this->cart) {
            return $this;
        }

        if ($previous = $this->cart) {
            $this->cart = null;
            $previous->removePayment($this);
        }

        if ($this->cart = $cart) {
            $this->cart->addPayment($this);
        }

        return $this;
    }

    public function getLocale(): ?string
    {
        if ($this->cart) {
            return $this->cart->getLocale();
        }

        return null;
    }
}

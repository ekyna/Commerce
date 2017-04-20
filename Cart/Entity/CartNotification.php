<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartNotificationInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractNotification;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class CartNotification
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartNotification extends AbstractNotification implements CartNotificationInterface
{
    protected ?CartInterface $cart = null;


    public function getSale(): ?SaleInterface
    {
        return $this->getCart();
    }

    public function setSale(?SaleInterface $sale): SaleNotificationInterface
    {
        if ($sale && !$sale instanceof CartInterface) {
            throw new UnexpectedTypeException($sale, CartInterface::class);
        }

        return $this->setCart($sale);
    }

    public function getCart(): ?CartInterface
    {
        return $this->cart;
    }

    public function setCart(?CartInterface $cart): CartNotificationInterface
    {
        if ($cart === $this->cart) {
            return $this;
        }

        if ($previous = $this->cart) {
            $this->cart = null;
            $previous->removeNotification($this);
        }

        if ($this->cart = $cart) {
            $this->cart->addNotification($this);
        }

        return $this;
    }
}

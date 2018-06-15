<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractNotification;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Model\CartNotificationInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;

/**
 * Class CartNotification
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartNotification extends AbstractNotification implements CartNotificationInterface
{
    /**
     * @var CartInterface
     */
    protected $cart;


    /**
     * @inheritdoc
     */
    public function getSale()
    {
        return $this->getCart();
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        if (null !== $sale && !$sale instanceof CartInterface) {
            throw new InvalidArgumentException('Expected instance of CartInterface');
        }

        $this->setCart($sale);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @inheritdoc
     */
    public function setCart(CartInterface $cart = null)
    {
        if ($cart !== $this->cart) {
            if ($previous = $this->cart) {
                $this->cart = null;
                $previous->removeNotification($this);
            }

            if ($this->cart = $cart) {
                $this->cart->addNotification($this);
            }
        }

        return $this;
    }
}

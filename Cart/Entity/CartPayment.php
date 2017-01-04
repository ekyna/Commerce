<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model;
use Ekyna\Component\Commerce\Payment\Entity\AbstractPayment;

/**
 * Class CartPayment
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartPayment extends AbstractPayment implements Model\CartPaymentInterface
{
    /**
     * @var Model\CartInterface
     */
    protected $cart;


    /**
     * @inheritdoc
     *
     * @return Model\CartInterface
     */
    public function getSale()
    {
        return $this->getCart();
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
    public function setCart(Model\CartInterface $cart = null)
    {
        if (null !== $this->cart && $this->cart != $cart) {
            $this->cart->removePayment($this);
        }

        $this->cart = $cart;

        return $this;
    }
}

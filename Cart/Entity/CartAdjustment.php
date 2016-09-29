<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAdjustment;
use Ekyna\Component\Commerce\Cart\Model\CartAdjustmentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;

/**
 * Class CartAdjustment
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAdjustment extends AbstractAdjustment implements CartAdjustmentInterface
{
    /**
     * @var CartInterface
     */
    protected $cart;


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
        if ($this->cart && $this->cart != $cart) {
            $this->cart->removeAdjustment($this);
        }

        $this->cart = $cart;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable()
    {
        return $this->cart;
    }
}

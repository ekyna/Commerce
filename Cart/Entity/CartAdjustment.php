<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAdjustment;
use Ekyna\Component\Commerce\Cart\Model\CartAdjustmentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Class CartAdjustment
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAdjustment extends AbstractSaleAdjustment implements CartAdjustmentInterface
{
    /**
     * @var CartInterface
     */
    protected $cart;


    /**
     * @inheritdoc
     */
    public function getSale(): ?SaleInterface
    {
        return $this->cart;
    }

    /**
     * @inheritdoc
     */
    public function getCart(): ?CartInterface
    {
        return $this->cart;
    }

    /**
     * @inheritdoc
     */
    public function setCart(CartInterface $cart = null): CartAdjustmentInterface
    {
        if ($cart !== $this->cart) {
            if ($previous = $this->cart) {
                $this->cart = null;
                $previous->removeAdjustment($this);
            }

            if ($this->cart = $cart) {
                $this->cart->addAdjustment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable(): AdjustableInterface
    {
        return $this->cart;
    }
}

<?php

declare(strict_types=1);

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
    protected ?CartInterface $cart = null;


    public function getSale(): ?SaleInterface
    {
        return $this->cart;
    }

    public function getCart(): ?CartInterface
    {
        return $this->cart;
    }

    public function setCart(?CartInterface $cart): CartAdjustmentInterface
    {
        if ($cart === $this->cart) {
            return $this;
        }

        if ($previous = $this->cart) {
            $this->cart = null;
            $previous->removeAdjustment($this);
        }

        if ($this->cart = $cart) {
            $this->cart->addAdjustment($this);
        }

        return $this;
    }

    public function getAdjustable(): AdjustableInterface
    {
        return $this->cart;
    }
}

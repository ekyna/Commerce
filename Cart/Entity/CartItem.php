<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartItemAdjustmentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;

/**
 * Class CartItem
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItem extends AbstractSaleItem implements CartItemInterface
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
        if (null === $cart = $this->getCart()) {
            $parent = $this;
            while (null !== $parent) {
                if (null !== $cart = $parent->getCart()) {
                    return $cart;
                }
                $parent = $parent->getParent();
            }
        }

        return $cart;
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        if ($sale && !$sale instanceof CartInterface) {
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
            $previous = $this->cart;
            $this->cart = $cart;

            if ($previous) {
                $previous->removeItem($this);
            }

            if ($this->cart) {
                $this->cart->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setParent(SaleItemInterface $parent = null)
    {
        if ($parent && !$parent instanceof CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
        }

        if ($parent !== $this->parent) {
            $previous = $this->parent;
            $this->parent = $parent;

            if ($previous) {
                $previous->removeChild($this);
            }

            if ($this->parent) {
                $this->parent->addChild($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addChild(SaleItemInterface $child)
    {
        if (!$child instanceof CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
        }

        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChild(SaleItemInterface $child)
    {
        if (!$child instanceof CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
        }

        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof CartItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemAdjustmentInterface.");
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof CartItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemAdjustmentInterface.");
        }

        if (!$this->adjustments->contains($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setItem($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof CartItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemAdjustmentInterface.");
        }

        if ($this->adjustments->contains($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setItem(null);
        }

        return $this;
    }
}

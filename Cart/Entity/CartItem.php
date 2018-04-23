<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Model;

/**
 * Class CartItem
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItem extends AbstractSaleItem implements Model\CartItemInterface
{
    /**
     * @var Model\CartInterface
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
    public function setSale(Common\SaleInterface $sale = null)
    {
        $sale && $this->assertSaleClass($sale);

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
    public function setCart(Model\CartInterface $cart = null)
    {
        if ($cart !== $this->cart) {
            if ($previous = $this->cart) {
                $this->cart = null;
                $previous->removeItem($this);
            }

            if ($this->cart = $cart) {
                $this->cart->addItem($this);
            }
        }

        return $this;
    }

    /**
     * Asserts that the given sale is an instance of the expected class.
     *
     * @param Common\SaleInterface $sale
     */
    protected function assertSaleClass(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Model\CartInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartInterface::class);
        }
    }

    /**
     * Asserts that the given sale item is an instance of the expected class.
     *
     * @param Common\SaleItemInterface $child
     */
    protected function assertItemClass(Common\SaleItemInterface $child)
    {
        if (!$child instanceof Model\CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartItemInterface::class);
        }
    }

    /**
     * Asserts that the given adjustment is an instance of the expected class.
     *
     * @param Common\AdjustmentInterface $adjustment
     */
    protected function assertItemAdjustmentClass(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\CartItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartItemAdjustmentInterface::class);
        }
    }
}

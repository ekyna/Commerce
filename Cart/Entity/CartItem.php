<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class CartItem
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItem extends AbstractSaleItem implements Model\CartItemInterface
{
    protected ?Model\CartInterface $cart = null;


    public function getSale(): ?Common\SaleInterface
    {
        if ($cart = $this->getCart()) {
            return $cart;
        }

        $parent = $this;
        while ($parent) {
            if ($cart = $parent->getCart()) {
                return $cart;
            }

            $parent = $parent->getParent();
        }

        return null;
    }

    /**
     * @param Model\CartInterface|null $sale
     */
    public function setSale(?Common\SaleInterface $sale): Common\SaleItemInterface
    {
        $sale && $this->assertSaleClass($sale);

        $this->setCart($sale);

        return $this;
    }

    public function getCart(): ?Model\CartInterface
    {
        return $this->cart;
    }

    public function setCart(?Model\CartInterface $cart): CartItemInterface
    {
        if ($cart === $this->cart) {
            return $this;
        }

        if ($previous = $this->cart) {
            $this->cart = null;
            $previous->removeItem($this);
        }

        if ($this->cart = $cart) {
            $this->cart->addItem($this);
        }

        return $this;
    }

    protected function assertSaleClass(Common\SaleInterface $sale): void
    {
        if (!$sale instanceof Model\CartInterface) {
            throw new UnexpectedTypeException($sale, Model\CartInterface::class);
        }
    }

    protected function assertItemClass(Common\SaleItemInterface $child): void
    {
        if (!$child instanceof Model\CartItemInterface) {
            throw new UnexpectedTypeException($child, Model\CartItemInterface::class);
        }
    }

    protected function assertItemAdjustmentClass(Common\AdjustmentInterface $adjustment): void
    {
        if (!$adjustment instanceof Model\CartItemAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\CartItemAdjustmentInterface::class);
        }
    }
}

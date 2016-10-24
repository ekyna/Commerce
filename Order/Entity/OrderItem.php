<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemAdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Class OrderItem
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItem extends AbstractSaleItem implements OrderItemInterface
{
    /**
     * @var OrderInterface
     */
    protected $order;


    /**
     * @inheritdoc
     */
    public function getSale()
    {
        if (null === $order = $this->getOrder()) {
            $parent = $this;
            while (null !== $parent) {
                if (null !== $order = $parent->getOrder()) {
                    return $order;
                }
                $parent = $parent->getParent();
            }
        }

        return $order;
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        if (null !== $sale && !$sale instanceof OrderInterface) {
            throw new InvalidArgumentException('Expected instance of OrderInterface');
        }

        $this->setOrder($sale);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function setOrder(OrderInterface $order = null)
    {
        if (null !== $this->order && $this->order != $order) {
            $this->order->removeItem($this);
        }

        $this->order = $order;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setParent(SaleItemInterface $parent = null)
    {
        if (!$parent instanceof OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addChild(SaleItemInterface $child)
    {
        if (!$child instanceof OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
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
        if (!$child instanceof OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
        }

        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            //$child->setParent(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof OrderItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemAdjustmentInterface.");
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof OrderItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemAdjustmentInterface.");
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
        if (!$adjustment instanceof OrderItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemAdjustmentInterface.");
        }

        if ($this->adjustments->contains($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            //$adjustment->setItem(null);
        }

        return $this;
    }
}

<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
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
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function setOrder(OrderInterface $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @inheritdoc
     * @internal
     */
    public function setParent(OrderItemInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addChild(OrderItemInterface $item)
    {
        if (!$this->children->contains($item)) {
            $item->setParent($this);
            $this->children->add($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChild(OrderItemInterface $item)
    {
        if (!$this->children->contains($item)) {
            $item->setParent(null);
            $this->children->removeElement($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(OrderItemAdjustmentInterface $adjustment)
    {
        if (!$this->adjustments->contains($adjustment)) {
            $adjustment->setItem($this);
            $this->adjustments->add($adjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(OrderItemAdjustmentInterface $adjustment)
    {
        if (!$this->adjustments->contains($adjustment)) {
            $adjustment->setItem(null);
            $this->adjustments->removeElement($adjustment);
        }

        return $this;
    }
}

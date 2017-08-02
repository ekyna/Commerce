<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemAdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;

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
     * @var ArrayCollection|StockAssignmentInterface[]
     */
    protected $stockAssignments;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->stockAssignments = new ArrayCollection();
    }

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
        if ($sale && !$sale instanceof OrderInterface) {
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
        if ($order !== $this->order) {
            $previous = $this->order;
            $this->order = $order;

            if ($previous) {
                $previous->removeItem($this);
            }

            if ($this->order) {
                $this->order->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addStockAssignment(StockAssignmentInterface $assignment)
    {
        if (!$this->stockAssignments->contains($assignment)) {
            $this->stockAssignments->add($assignment);
            $assignment->setSaleItem($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeStockAssignment(StockAssignmentInterface $assignment)
    {
        if ($this->stockAssignments->contains($assignment)) {
            $this->stockAssignments->removeElement($assignment);
            $assignment->setSaleItem(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasStockAssignments()
    {
        return 0 < $this->stockAssignments->count();
    }

    /**
     * @inheritdoc
     */
    public function getStockAssignments()
    {
        return $this->stockAssignments;
    }

    /**
     * @inheritdoc
     */
    public function setParent(SaleItemInterface $parent = null)
    {
        if ($parent && !$parent instanceof OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
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
     * @inheritDoc
     */
    public function createChild()
    {
        $child = new static;

        $this->addChild($child);

        return $child;
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
            $child->setParent(null);
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
            $adjustment->setItem(null);
        }

        return $this;
    }
}

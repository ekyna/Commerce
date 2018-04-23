<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;

/**
 * Class OrderItem
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItem extends AbstractSaleItem implements Model\OrderItemInterface
{
    /**
     * @var Model\OrderInterface
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
    public function setSale(Common\SaleInterface $sale = null)
    {
        $sale && $this->assertSaleClass($sale);

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
    public function setOrder(Model\OrderInterface $order = null)
    {
        if ($order !== $this->order) {
            if ($previous = $this->order) {
                $this->order = null;
                $previous->removeItem($this);
            }

            if ($this->order = $order) {
                $this->order->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasStockAssignment(StockAssignmentInterface $assignment)
    {
        return $this->stockAssignments->contains($assignment);
    }

    /**
     * @inheritdoc
     */
    public function addStockAssignment(StockAssignmentInterface $assignment)
    {
        if (!$this->hasStockAssignment($assignment)) {
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
        if ($this->hasStockAssignment($assignment)) {
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
    protected function assertSaleClass(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Model\OrderInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderInterface::class);
        }
    }

    /**
     * @inheritdoc
     */
    protected function assertItemClass(Common\SaleItemInterface $child)
    {
        if (!$child instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderItemInterface::class);
        }
    }

    /**
     * @inheritdoc
     */
    protected function assertItemAdjustmentClass(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\OrderItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderItemAdjustmentInterface::class);
        }
    }
}

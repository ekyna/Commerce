<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemStockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockAssignment;

/**
 * Class OrderItemStockAssignment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemStockAssignment extends AbstractStockAssignment implements OrderItemStockAssignmentInterface
{
    /**
     * @var OrderItemInterface
     */
    protected $orderItem;


    /**
     * @inheritdoc
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @inheritdoc
     */
    public function setOrderItem(OrderItemInterface $orderItem = null)
    {
        if ($orderItem != $this->orderItem) {
            $previous = $this->orderItem;
            $this->orderItem = $orderItem;

            if ($previous) {
                $previous->removeStockAssignment($this);
            }

            if ($this->orderItem) {
                $this->orderItem->addStockAssignment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSaleItem()
    {
        return $this->getOrderItem();
    }

    /**
     * @inheritdoc
     */
    public function setSaleItem(SaleItemInterface $saleItem = null)
    {
        if ($saleItem && !$saleItem instanceof OrderItemInterface) {
            throw new InvalidArgumentException(sprintf("Expected instance of '%s'.", OrderItemInterface::class));
        }

        return $this->setOrderItem($saleItem);
    }
}

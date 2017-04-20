<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemStockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockAssignment;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;

/**
 * Class OrderItemStockAssignment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemStockAssignment extends AbstractStockAssignment implements OrderItemStockAssignmentInterface
{
    protected ?OrderItemInterface $orderItem = null;

    public function getOrderItem(): ?OrderItemInterface
    {
        return $this->orderItem;
    }

    public function setOrderItem(?OrderItemInterface $orderItem): OrderItemStockAssignmentInterface
    {
        if ($orderItem === $this->orderItem) {
            return $this;
        }

        if ($previous = $this->orderItem) {
            $this->orderItem = null;
            $previous->removeStockAssignment($this);
        }

        if ($this->orderItem = $orderItem) {
            $this->orderItem->addStockAssignment($this);
        }

        return $this;
    }

    public function getSaleItem(): ?SaleItemInterface
    {
        return $this->getOrderItem();
    }

    public function setSaleItem(?SaleItemInterface $saleItem): StockAssignmentInterface
    {
        if ($saleItem && !$saleItem instanceof OrderItemInterface) {
            throw new UnexpectedTypeException($saleItem, OrderItemInterface::class);
        }

        return $this->setOrderItem($saleItem);
    }
}

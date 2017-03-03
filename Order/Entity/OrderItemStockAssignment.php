<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockAssignment;

/**
 * Class OrderItemStockAssignment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemStockAssignment extends AbstractStockAssignment
{
    /**
     * @var OrderItemInterface
     */
    protected $orderItem;


    /**
     * Returns the order item.
     *
     * @return OrderItemInterface
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * Sets the order item.
     *
     * @param OrderItemInterface $orderItem
     *
     * @return OrderItemStockAssignment
     */
    public function setOrderItem(OrderItemInterface $orderItem = null)
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSaleItem()
    {
        return $this->getOrderItem();
    }

    /**
     * @inheritDoc
     */
    public function setSaleItem(SaleItemInterface $saleItem = null)
    {
        if ($saleItem && !$saleItem instanceof OrderItemInterface) {
            throw new InvalidArgumentException(sprintf("Expected instance of '%s'.", OrderItemInterface::class));
        }

        return $this->setOrderItem($saleItem);
    }
}

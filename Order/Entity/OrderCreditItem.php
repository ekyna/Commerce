<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Credit\Entity\AbstractCreditItem;
use Ekyna\Component\Commerce\Credit\Model\CreditInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Class OrderCreditItem
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderCreditItem extends AbstractCreditItem implements Model\OrderCreditItemInterface
{
    /**
     * @var Model\OrderItemInterface
     */
    protected $orderItem;

    /**
     * @var Model\OrderShipmentItemInterface
     */
    protected $shipmentItem;


    /**
     * @inheritdoc
     */
    public function setCredit(CreditInterface $credit = null)
    {
        if (null !== $credit && !$credit instanceof Model\OrderCreditInterface) {
            throw new InvalidArgumentException("Expected instance of OrderCreditInterface.");
        }

        return parent::setCredit($credit);
    }

    /**
     * @inheritdoc
     */
    public function setOrderItem(Model\OrderItemInterface $orderItem)
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Model\OrderItemInterface
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @inheritdoc
     */
    public function setShipmentItem(ShipmentItemInterface $shipmentItem = null)
    {
        if (null !== $shipmentItem && !$shipmentItem instanceof Model\OrderShipmentItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentItemInterface.");
        }


        $this->shipmentItem = $shipmentItem;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Model\OrderShipmentItemInterface
     */
    public function getShipmentItem()
    {
        return $this->shipmentItem;
    }

    /**
     * @inheritDoc
     */
    public function setSaleItem(SaleItemInterface $saleItem)
    {
        if (!$saleItem instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
        }

        return $this->setOrderItem($saleItem);
    }

    /**
     * @inheritdoc
     */
    public function getSaleItem()
    {
        return $this->getOrderItem();
    }
}

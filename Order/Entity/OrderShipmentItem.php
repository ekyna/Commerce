<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipmentItem;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Class OrderShipmentItem
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItem extends AbstractShipmentItem implements OrderShipmentItemInterface
{
    protected ?OrderItemInterface $orderItem;

    public function setShipment(?ShipmentInterface $shipment): ShipmentItemInterface
    {
        if ($shipment && !$shipment instanceof OrderShipmentInterface) {
            throw new UnexpectedTypeException($shipment, OrderShipmentInterface::class);
        }

        return parent::setShipment($shipment);
    }

    public function setOrderItem(?OrderItemInterface $orderItem): OrderShipmentItemInterface
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    public function getOrderItem(): ?OrderItemInterface
    {
        return $this->orderItem;
    }

    public function setSaleItem(?SaleItemInterface $saleItem): ShipmentItemInterface
    {
        if (!$saleItem instanceof OrderItemInterface) {
            throw new UnexpectedTypeException($saleItem, OrderItemInterface::class);
        }

        return $this->setOrderItem($saleItem);
    }

    public function getSaleItem(): ?SaleItemInterface
    {
        return $this->getOrderItem();
    }
}

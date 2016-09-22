<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipmentItem;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class OrderShipmentItem
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItem extends AbstractShipmentItem implements OrderShipmentItemInterface
{
    /**
     * @inheritdoc
     */
    public function setShipment(ShipmentInterface $shipment = null)
    {
        if ((null !== $shipment) && !$shipment instanceof OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentInterface.");
        }

        return parent::setShipment($shipment);
    }
}

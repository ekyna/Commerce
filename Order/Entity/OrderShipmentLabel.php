<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipmentLabel;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentParcelInterface;

/**
 * Class OrderShipmentLabel
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentLabel extends AbstractShipmentLabel
{
    /**
     * @inheritDoc
     */
    public function setShipment(ShipmentInterface $shipment = null)
    {
        if ($shipment && !$shipment instanceof OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentInterface::class);
        }

        return parent::setShipment($shipment);
    }

    /**
     * @inheritDoc
     */
    public function setParcel(ShipmentParcelInterface $parcel = null)
    {
        if ($parcel && !$parcel instanceof OrderShipmentParcel) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentParcel::class);
        }

        return parent::setParcel($parcel);
    }
}

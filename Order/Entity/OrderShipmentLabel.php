<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipmentLabel;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentParcelInterface;

/**
 * Class OrderShipmentLabel
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentLabel extends AbstractShipmentLabel
{
    public function setShipment(?ShipmentInterface $shipment): ShipmentLabelInterface
    {
        if ($shipment && !$shipment instanceof OrderShipmentInterface) {
            throw new UnexpectedTypeException($shipment, OrderShipmentInterface::class);
        }

        return parent::setShipment($shipment);
    }

    public function setParcel(?ShipmentParcelInterface $parcel): ShipmentLabelInterface
    {
        if ($parcel && !$parcel instanceof OrderShipmentParcel) {
            throw new InvalidArgumentException($parcel, OrderShipmentParcel::class);
        }

        return parent::setParcel($parcel);
    }
}

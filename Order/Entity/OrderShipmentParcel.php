<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipmentParcel;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentDataInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentParcelInterface;

/**
 * Class OrderShipmentParcel
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentParcel extends AbstractShipmentParcel
{
    public function setShipment(?ShipmentInterface $shipment): ShipmentParcelInterface
    {
        if ($shipment && !$shipment instanceof OrderShipmentInterface) {
            throw new UnexpectedTypeException($shipment, OrderShipmentInterface::class);
        }

        return parent::setShipment($shipment);
    }

    public function addLabel(ShipmentLabelInterface $label): ShipmentDataInterface
    {
        if (!$label instanceof OrderShipmentLabel) {
            throw new UnexpectedTypeException($label, OrderShipmentLabel::class);
        }

        return parent::addLabel($label);
    }

    public function removeLabel(ShipmentLabelInterface $label): ShipmentDataInterface
    {
        if (!$label instanceof OrderShipmentLabel) {
            throw new UnexpectedTypeException($label, OrderShipmentLabel::class);
        }

        return parent::removeLabel($label);
    }
}

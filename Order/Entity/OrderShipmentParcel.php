<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipmentParcel;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;

/**
 * Class OrderShipmentParcel
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentParcel extends AbstractShipmentParcel
{
    /**
     * @inheritdoc
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
    public function addLabel(ShipmentLabelInterface $label)
    {
        if (!$label instanceof OrderShipmentLabel) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentLabel::class);
        }

        return parent::addLabel($label);
    }

    /**
     * @inheritDoc
     */
    public function removeLabel(ShipmentLabelInterface $label)
    {
        if (!$label instanceof OrderShipmentLabel) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentLabel::class);
        }

        return parent::removeLabel($label);
    }
}

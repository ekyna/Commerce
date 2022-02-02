<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Ekyna\Component\Commerce\Shipment\Model;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class AbstractShipmentParcel
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentParcel extends AbstractResource implements Model\ShipmentParcelInterface
{
    use Model\ShipmentDataTrait;

    protected ?Model\ShipmentInterface $shipment = null;

    public function __construct()
    {
        $this->initializeShipmentData();
    }

    public function getShipment(): ?Model\ShipmentInterface
    {
        return $this->shipment;
    }

    public function setShipment(?Model\ShipmentInterface $shipment): Model\ShipmentParcelInterface
    {
        if ($this->shipment === $shipment) {
            return $this;
        }

        if ($previous = $this->shipment) {
            $this->shipment = null;
            $previous->removeParcel($this);
        }

        if ($this->shipment = $shipment) {
            $this->shipment->addParcel($this);
        }

        return $this;
    }
}

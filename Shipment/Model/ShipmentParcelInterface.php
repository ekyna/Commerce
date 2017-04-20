<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ShipmentParcelInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentParcelInterface extends ResourceInterface, ShipmentDataInterface
{
    public function getShipment(): ?ShipmentInterface;

    public function setShipment(?ShipmentInterface $shipment): ShipmentParcelInterface;
}

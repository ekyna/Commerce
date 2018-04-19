<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ShipmentParcelInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentParcelInterface extends ResourceInterface, ShipmentDataInterface
{
    /**
     * Returns the shipment.
     *
     * @return ShipmentInterface
     */
    public function getShipment();

    /**
     * Sets the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return $this|ShipmentParcelInterface
     */
    public function setShipment(ShipmentInterface $shipment = null);
}

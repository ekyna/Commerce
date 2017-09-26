<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Shipment\Model;

/**
 * Interface WeightCalculatorInterface
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WeightCalculatorInterface
{
    /**
     * Calculate the shipment total weight.
     *
     * @param Model\ShipmentInterface $shipment
     *
     * @return float
     */
    public function calculateShipment(Model\ShipmentInterface $shipment);

    /**
     * Calculate the shipment item total weight.
     *
     * @param Model\ShipmentItemInterface $item
     *
     * @return float
     */
    public function calculateShipmentItem(Model\ShipmentItemInterface $item);
}

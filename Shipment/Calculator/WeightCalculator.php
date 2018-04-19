<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class WeightCalculator
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WeightCalculator implements WeightCalculatorInterface
{
    /**
     * @inheritdoc
     */
    public function calculateShipment(Model\ShipmentInterface $shipment)
    {
        $total = .0;

        if ($shipment->hasParcels()) {
            foreach ($shipment->getParcels() as $parcel) {
                $total += $parcel->getWeight();
            }

            return $total;
        }

        foreach ($shipment->getItems() as $item) {
            $total += $this->calculateShipmentItem($item);
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateShipmentItem(Model\ShipmentItemInterface $item)
    {
        return $item->getSaleItem()->getWeight() * $item->getQuantity();
    }
}

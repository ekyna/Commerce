<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class WeightCalculator
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WeightCalculator implements WeightCalculatorInterface
{
    public function calculateShipment(Model\ShipmentInterface $shipment): Decimal
    {
        $total = new Decimal(0);

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

    public function calculateShipmentItem(Model\ShipmentItemInterface $item): Decimal
    {
        return $item->getSaleItem()->getWeight()->mul($item->getQuantity());
    }
}

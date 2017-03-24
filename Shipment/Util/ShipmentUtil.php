<?php

namespace Ekyna\Component\Commerce\Shipment\Util;

use Ekyna\Component\Commerce\Shipment\Model;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;

/**
 * Class ShipmentUtil
 * @package Ekyna\Component\Commerce\Shipment\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentUtil
{
    /**
     * Calculate the shipment item available quantity.
     *
     * @param Model\ShipmentItemInterface $item
     *
     * @return float
     */
    static public function calculateAvailableQuantity(Model\ShipmentItemInterface $item)
    {
        $saleItem = $item->getSaleItem();

        if (!$saleItem instanceof StockAssignmentsInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($saleItem->getStockAssignments() as $assignment) {
            $quantity += $assignment->getShippableQuantity();
        }

        // If shipment is in stockable state, this shipment item's quantity
        // is considered as shipped.
        // TODO Test. Multiple shipment items can point to the same subject ...
        if (Model\ShipmentStates::isStockableState($item->getShipment())) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }

    /**
     * Calculates the shipment item expected quantity.
     *
     * @param Model\ShipmentItemInterface $item
     *
     * @return float
     */
    static public function calculateExpectedQuantity(Model\ShipmentItemInterface $item)
    {
        $saleItem = $item->getSaleItem();

        $quantity = $saleItem->getTotalQuantity();

        /** @var Model\ShipmentSubjectInterface $sale */
        $sale = $saleItem->getSale();

        foreach ($sale->getShipments() as $shipment) {
            // Skip this shipment
            if ($shipment === $item->getShipment()) {
                continue;
            }

            // Skip if shipment is cancelled
            if ($shipment->getState() === Model\ShipmentStates::STATE_CANCELLED) {
                continue;
            }

            // Find matching sale item
            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $saleItem) {
                    $quantity -= $shipmentItem->getQuantity();
                }
            }

            // TODO watch for returned Shipments
        }

        // If shipment is in stockable state, this shipment item's quantity
        // is considered as shipped.
        // TODO Test. Multiple shipment items can point to the same subject ...
        if (Model\ShipmentStates::isStockableState($item->getShipment())) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }
}

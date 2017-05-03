<?php

namespace Ekyna\Component\Commerce\Shipment\Util;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Model;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;

/**
 * Class ShipmentUtil
 * @package Ekyna\Component\Commerce\Shipment\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class ShipmentUtil
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
        if (Model\ShipmentStates::isStockableState($item->getShipment()->getState())) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }

    /**
     * Calculates the shipment item shippable quantity.
     *
     * @param Model\ShipmentItemInterface $item
     *
     * @return float
     */
    static public function calculateShippableQuantity(Model\ShipmentItemInterface $item)
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
                    if ($shipment->isReturn()) {
                        $quantity += $shipmentItem->getQuantity();
                    } else {
                        $quantity -= $shipmentItem->getQuantity();
                    }
                }
            }

            // TODO watch for returned Shipments
        }

        // If shipment is in stockable state, this shipment item's quantity
        // is considered as shipped.
        // TODO Test. Multiple shipment items can point to the same subject ...
        /*$shipment = $item->getShipment();
        if (Model\ShipmentStates::isStockableState($shipment->getState())) {
            if ($shipment->isReturn()) {
                $quantity -= $item->getQuantity();
            } else {
                $quantity += $item->getQuantity();
            }
        }*/

        return $quantity;
    }

    /**
     * Calculates the shipment item returnable quantity.
     *
     * @param Model\ShipmentItemInterface $item
     *
     * @return float
     */
    static public function calculateReturnableQuantity(Model\ShipmentItemInterface $item)
    {
        $saleItem = $item->getSaleItem();

        $quantity = 0;

        /** @var Model\ShipmentSubjectInterface $sale */
        $sale = $saleItem->getSale();

        foreach ($sale->getShipments() as $shipment) {
            // Skip this shipment
            if ($shipment === $item->getShipment()) {
                continue;
            }

            // Skip if shipment is cancelled
            if (!Model\ShipmentStates::isShippedState($shipment->getState())) {
                continue;
            }

            // Find matching sale item
            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $saleItem) {
                    if ($shipment->isReturn()) {
                        $quantity -= $shipmentItem->getQuantity();
                    } else {
                        $quantity += $shipmentItem->getQuantity();
                    }
                }
            }
        }

        // If shipment is in stockable state, this shipment item's quantity
        // is considered as shipped.
        // TODO Test. Multiple shipment items can point to the same subject ...
        if (Model\ShipmentStates::isStockableState($item->getShipment())) {
            if ($item->getShipment()->isReturn()) {
                $quantity += $item->getQuantity();
            } else {
                $quantity -= $item->getQuantity();
            }
        }

        return $quantity;
    }

    /**
     * Calculates the shipped quantity for the given sale item.
     *
     * @param SaleItemInterface $saleItem
     *
     * @return float
     */
    static public function calculateShippedQuantity(SaleItemInterface $saleItem)
    {
        /** @var SaleInterface $sale */
        $sale = $saleItem->getSale();

        if (!$sale instanceof Model\ShipmentSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getShipments() as $shipment) {
            if (!Model\ShipmentStates::isShippedState($shipment->getState())) {
                continue;
            }

            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $saleItem) {
                    if ($shipment->isReturn()) {
                        $quantity -= $shipmentItem->getQuantity();
                    } else {
                        $quantity += $shipmentItem->getQuantity();
                    }
                }
            }
        }

        return $quantity;
    }

    /**
     * Calculates the returned quantity for the given sale item.
     *
     * @param SaleItemInterface $saleItem
     *
     * @return float
     */
    static public function calculateReturnedQuantity(SaleItemInterface $saleItem)
    {
        /** @var SaleInterface $sale */
        $sale = $saleItem->getSale();

        if (!$sale instanceof Model\ShipmentSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getShipments() as $shipment) {
            if (!Model\ShipmentStates::isShippedState($shipment->getState())) {
                continue;
            }

            if (!$shipment->isReturn()) {
                continue;
            }

            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $saleItem) {
                    $quantity += $shipmentItem->getQuantity();
                }
            }
        }

        return $quantity;
    }
}

<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class QuantityCalculator
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuantityCalculator implements QuantityCalculatorInterface
{
    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     */
    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * @inheritdoc
     */
    public function calculateAvailableQuantity(Shipment\ShipmentItemInterface $item)
    {
        $saleItem = $item->getSaleItem();

        if (!$this->hasStockableSubject($saleItem)) {
            return INF;
        }

        $quantity = 0;

        /** @var StockAssignmentsInterface $saleItem */
        foreach ($saleItem->getStockAssignments() as $assignment) {
            $quantity += $assignment->getShippableQuantity();
        }

        // If shipment is in stockable state, this shipment item's quantity
        // is considered as shipped.
        // TODO Test. Multiple shipment items can point to the same subject ...
        if (Shipment\ShipmentStates::isStockableState($item->getShipment()->getState())) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateShippableQuantity(Shipment\ShipmentItemInterface $item)
    {
        $saleItem = $item->getSaleItem();

        $quantity = $saleItem->getTotalQuantity();

        /** @var Shipment\ShipmentSubjectInterface $sale */
        $sale = $saleItem->getSale();

        foreach ($sale->getShipments() as $shipment) {
            // Skip this shipment
            if ($shipment === $item->getShipment()) {
                continue;
            }

            // Skip if shipment is cancelled
            if ($shipment->getState() === Shipment\ShipmentStates::STATE_CANCELLED) {
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
        if (Shipment\ShipmentStates::isStockableState($shipment->getState())) {
            if ($shipment->isReturn()) {
                $quantity -= $item->getQuantity();
            } else {
                $quantity += $item->getQuantity();
            }
        }*/

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateReturnableQuantity(Shipment\ShipmentItemInterface $item)
    {
        $saleItem = $item->getSaleItem();

        $quantity = 0;

        /** @var Shipment\ShipmentSubjectInterface $sale */
        $sale = $saleItem->getSale();

        foreach ($sale->getShipments() as $shipment) {
            // Skip this shipment
            if ($shipment === $item->getShipment()) {
                continue;
            }

            // Skip if shipment is cancelled
            if (!Shipment\ShipmentStates::isShippedState($shipment->getState())) {
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
        if (Shipment\ShipmentStates::isStockableState($item->getShipment())) {
            if ($item->getShipment()->isReturn()) {
                $quantity += $item->getQuantity();
            } else {
                $quantity -= $item->getQuantity();
            }
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateShippedQuantity(Common\SaleItemInterface $saleItem)
    {
        /** @var Common\SaleInterface $sale */
        $sale = $saleItem->getSale();

        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getShipments() as $shipment) {
            if (!Shipment\ShipmentStates::isShippedState($shipment->getState())) {
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
     * @inheritdoc
     */
    public function calculateReturnedQuantity(Common\SaleItemInterface $saleItem)
    {
        /** @var Common\SaleInterface $sale */
        $sale = $saleItem->getSale();

        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getShipments() as $shipment) {
            if (!Shipment\ShipmentStates::isShippedState($shipment->getState())) {
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

    /**
     * Returns whether or not the sale item has a stockable subject.
     *
     * @param Common\SaleItemInterface $saleItem
     *
     * @return bool
     */
    private function hasStockableSubject(Common\SaleItemInterface $saleItem)
    {
        if (!$saleItem instanceof StockAssignmentsInterface) {
            return false;
        }

        if (null === $subject = $this->subjectHelper->resolve($saleItem)) {
            return false;
        }

        if (!$subject instanceof StockSubjectInterface) {
            return false;
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_INHERITED) {
            return false;
        }

        return true;
    }
}

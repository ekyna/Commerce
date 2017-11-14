<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

/**
 * Class ShipmentCalculator
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentCalculator implements ShipmentCalculatorInterface
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

        /**
         * Calculates the shipped quantity by ignoring returns, canceled shipments and this shipment
         */
        $shippedQuantity = 0;

        /** @var Shipment\ShipmentSubjectInterface $sale */
        $sale = $saleItem->getSale();
        foreach ($sale->getShipments() as $shipment) {
            // Skip returns and this shipment
            if ($shipment->isReturn() || $shipment === $item->getShipment()) {
                continue;
            }

            // Skip if shipment is canceled
            if ($shipment->getState() === Shipment\ShipmentStates::STATE_CANCELED) {
                continue;
            }

            // Find matching sale item
            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $saleItem) {
                    $shippedQuantity += $shipmentItem->getQuantity();
                }
            }
        }

        $stockUnits = [];
        /** @var Stock\StockAssignmentsInterface $saleItem */
        /** @var Stock\StockAssignmentInterface[] $assignments */
        $assignments = $saleItem->getStockAssignments()->toArray();

        /**
         * Calculates this sale item assignments sold quantity.
         */
        $assignmentSold = 0;
        foreach ($assignments as $assignment) {
            $assignmentSold += $assignment->getSoldQuantity();

            // Store distinct stock units
            if (!in_array($stockUnit = $assignment->getStockUnit(), $stockUnits, true)) {
                $stockUnits[] = $stockUnit;
            }
        }

        /**
         * Calculates the assigned stock units's available quantity
         * by ignoring this sale item's assignments.
         */
        $stockUnitSold = $stockUnitReceived = $stockUnitShipped = 0;
        /** @var Stock\StockUnitInterface $stockUnit */
        foreach ($stockUnits as $stockUnit) {
            $stockUnitSold += $stockUnit->getSoldQuantity();
            $stockUnitReceived += $stockUnit->getReceivedQuantity();

            foreach ($stockUnit->getStockAssignments() as $assignment) {
                // Skip this sale item's assignments
                if (in_array($assignment, $assignments, true)) {
                    continue;
                }
                $stockUnitShipped += $assignment->getShippedQuantity();
            }
        }
        $stockUnitAvailable = min($stockUnitReceived, $stockUnitSold) - $stockUnitShipped;
        if (0 > $stockUnitAvailable) $stockUnitAvailable = 0;

        $available = min($assignmentSold, $stockUnitAvailable) - $shippedQuantity;
        if (0 > $available) $available = 0;

        return $available;
    }

    /**
     * @inheritdoc
     */
    public function calculateShippableQuantity(Shipment\ShipmentItemInterface $item)
    {
        $saleItem = $item->getSaleItem();

        // TODO return zero if not shippable

        $quantity = $saleItem->getTotalQuantity();

        /** @var Shipment\ShipmentSubjectInterface $sale */
        $sale = $saleItem->getSale();

        foreach ($sale->getShipments() as $shipment) {
            // Skip returns and this shipment
            if ($shipment->isReturn() || $shipment === $item->getShipment()) {
                continue;
            }

            // Skip if shipment is canceled
            if ($shipment->getState() === Shipment\ShipmentStates::STATE_CANCELED) {
                continue;
            }

            // Find matching sale item
            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $saleItem) {
                    $quantity -= $shipmentItem->getQuantity();
                }
            }
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

        // TODO return zero if not shippable

        $quantity = 0;

        /** @var Shipment\ShipmentSubjectInterface $sale */
        $sale = $saleItem->getSale();

        foreach ($sale->getShipments() as $shipment) {
            // Skip this shipment
            if ($shipment === $item->getShipment()) {
                continue;
            }

            // Skip if shipment is shipped/returned
            if (!Shipment\ShipmentStates::isDone($shipment)) {
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
        $sale = $saleItem->getSale();

        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getShipments() as $shipment) {
            if (!Shipment\ShipmentStates::isDone($shipment)) {
                continue;
            }

            foreach ($shipment->getItems() as $line) {
                if ($line->getSaleItem() === $saleItem) {
                    $quantity += $shipment->isReturn() ? -$line->getQuantity() : $line->getQuantity();
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
        $sale = $saleItem->getSale();

        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getShipments() as $shipment) {
            if (!($shipment->isReturn() && Shipment\ShipmentStates::isDone($shipment))) {
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
     * @inheritdoc
     */
    public function buildShipmentQuantityMap(Shipment\ShipmentSubjectInterface $subject)
    {
        $quantities = [];

        if ($subject instanceof Common\SaleInterface) {
            foreach ($subject->getItems() as $item) {
                $this->buildSaleItemQuantities($item, $quantities);
            }
        }

        return $quantities;
    }

    /**
     * Builds the sale item quantities recursively.
     *
     * @param Common\SaleItemInterface $item
     * @param array                    $quantities
     */
    private function buildSaleItemQuantities(Common\SaleItemInterface $item, array &$quantities)
    {
        if (!$item->isCompound()) {
            $quantities[$item->getId()] = [
                'sold'     => $item->getTotalQuantity(),
                'shipped'  => $this->calculateShippedQuantity($item),
                'returned' => $this->calculateReturnedQuantity($item),
            ];
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildSaleItemQuantities($child, $quantities);
            }
        }
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
        if (!$saleItem instanceof Stock\StockAssignmentsInterface) {
            return false;
        }

        if (null === $subject = $this->subjectHelper->resolve($saleItem)) {
            return false;
        }

        if (!$subject instanceof Stock\StockSubjectInterface) {
            return false;
        }

        if ($subject->isStockCompound()) {
            return false;
        }

        if ($subject->getStockMode() === Stock\StockSubjectModes::MODE_DISABLED) {
            return false;
        }

        return true;
    }
}

<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Interface ShipmentSubjectCalculatorInterface
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentSubjectCalculatorInterface
{
    /**
     * Returns whether or not the given sale item is shipped (present in any shipment or return).
     *
     * @param Common\SaleItemInterface $saleItem
     *
     * @return bool
     */
    public function isShipped(Common\SaleItemInterface $saleItem): bool;

    /**
     * Calculate the shipment item available quantity.
     *
     * @param Common\SaleItemInterface   $saleItem
     * @param Shipment\ShipmentInterface $ignore
     *
     * @return float
     */
    public function calculateAvailableQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ): float;

    /**
     * Calculates the shipment item shippable quantity.
     *
     * @param Common\SaleItemInterface   $saleItem
     * @param Shipment\ShipmentInterface $ignore
     *
     * @return float
     */
    public function calculateShippableQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ): float;

    /**
     * Calculates the shipment item returnable quantity.
     *
     * @param Common\SaleItemInterface   $saleItem
     * @param Shipment\ShipmentInterface $ignore
     *
     * @return float
     */
    public function calculateReturnableQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ): float;

    /**
     * Calculates the shipped quantity for the given sale item.
     *
     * @param Common\SaleItemInterface   $saleItem
     * @param Shipment\ShipmentInterface $ignore
     *
     * @return float
     */
    public function calculateShippedQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ): float;

    /**
     * Calculates the returned quantity for the given sale item.
     *
     * @param Common\SaleItemInterface   $saleItem
     * @param Shipment\ShipmentInterface $ignore
     *
     * @return float
     */
    public function calculateReturnedQuantity(
        Common\SaleItemInterface $saleItem,
        Shipment\ShipmentInterface $ignore = null
    ): float;

    /**
     * Builds the shipment quantity map.
     *
     * [
     *     (int) sale item id => [
     *         'sold'     => (float) quantity,
     *         'shipped'  => (float) quantity,
     *         'returned' => (float) quantity,
     *     ]
     * ]
     *
     * @param Shipment\ShipmentSubjectInterface $subject
     *
     * @return array
     */
    public function buildShipmentQuantityMap(Shipment\ShipmentSubjectInterface $subject): array;

    /**
     * Builds the remaining sale items list.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return Shipment\RemainingList
     */
    public function buildRemainingList(Shipment\ShipmentInterface $shipment): Shipment\RemainingList;
}

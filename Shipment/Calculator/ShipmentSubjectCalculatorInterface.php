<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface as SaleItem;
use Ekyna\Component\Commerce\Shipment\Model\RemainingList;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface as Shipment;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface as Subject;

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
     * @param SaleItem $saleItem
     *
     * @return bool
     */
    public function isShipped(SaleItem $saleItem): bool;

    /**
     * Calculate the shipment item available quantity.
     *
     * @param SaleItem      $saleItem
     * @param Shipment|null $ignore
     *
     * @return float
     */
    public function calculateAvailableQuantity(SaleItem $saleItem, Shipment $ignore = null): float;

    /**
     * Calculates the shipment item shippable quantity.
     *
     * @param SaleItem      $saleItem
     * @param Shipment|null $ignore
     *
     * @return float
     */
    public function calculateShippableQuantity(SaleItem $saleItem, Shipment $ignore = null): float;

    /**
     * Calculates the shipment item returnable quantity.
     *
     * @param SaleItem      $saleItem
     * @param Shipment|null $ignore
     *
     * @return float
     */
    public function calculateReturnableQuantity(SaleItem $saleItem, Shipment $ignore = null): float;

    /**
     * Calculates the shipped quantity for the given sale item.
     *
     * @param SaleItem      $saleItem
     * @param Shipment|null $ignore
     *
     * @return float
     */
    public function calculateShippedQuantity(SaleItem $saleItem, Shipment $ignore = null): float;

    /**
     * Calculates the returned quantity for the given sale item.
     *
     * @param SaleItem      $saleItem
     * @param Shipment|null $ignore
     *
     * @return float
     */
    public function calculateReturnedQuantity(SaleItem $saleItem, Shipment $ignore = null): float;

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
     * @param Subject $subject
     *
     * @return array
     */
    public function buildShipmentQuantityMap(Subject $subject): array;

    /**
     * Builds the remaining sale items list.
     *
     * @param Shipment $shipment
     *
     * @return RemainingList
     */
    public function buildRemainingList(Shipment $shipment): RemainingList;
}

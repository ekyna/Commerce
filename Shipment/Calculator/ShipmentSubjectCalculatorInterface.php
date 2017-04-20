<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Decimal\Decimal;
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
     * Returns whether the given sale item is shipped (present in any shipment or return).
     */
    public function isShipped(SaleItem $saleItem): bool;

    /**
     * Calculate the shipment item available quantity.
     */
    public function calculateAvailableQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal;

    /**
     * Calculates the shipment item shippable quantity.
     */
    public function calculateShippableQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal;

    /**
     * Calculates the shipment item returnable quantity.
     */
    public function calculateReturnableQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal;

    /**
     * Calculates the shipped quantity for the given sale item.
     */
    public function calculateShippedQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal;

    /**
     * Calculates the returned quantity for the given sale item.
     */
    public function calculateReturnedQuantity(SaleItem $saleItem, Shipment $ignore = null): Decimal;

    /**
     * Builds the shipment quantity map.
     *
     * [
     *     (int) sale item id => [
     *         'sold'     => (Decimal) quantity,
     *         'invoiced' => (Decimal) quantity,
     *         'shipped'  => (Decimal) quantity,
     *         'returned' => (Decimal) quantity,
     *     ]
     * ]
     *
     * @return array<int, array<string, Decimal>>
     */
    public function buildShipmentQuantityMap(Subject $subject): array;

    /**
     * Builds the remaining sale items list.
     */
    public function buildRemainingList(Shipment $shipment): RemainingList;
}

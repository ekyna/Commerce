<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Interface ShipmentCalculatorInterface
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentCalculatorInterface
{
    /**
     * Calculate the shipment item available quantity.
     *
     * @param Shipment\ShipmentItemInterface $item
     *
     * @return float
     */
    public function calculateAvailableQuantity(Shipment\ShipmentItemInterface $item);

    /**
     * Calculates the shipment item shippable quantity.
     *
     * @param Shipment\ShipmentItemInterface $item
     *
     * @return float
     */
    public function calculateShippableQuantity(Shipment\ShipmentItemInterface $item);

    /**
     * Calculates the shipment item returnable quantity.
     *
     * @param Shipment\ShipmentItemInterface $item
     *
     * @return float
     */
    public function calculateReturnableQuantity(Shipment\ShipmentItemInterface $item);

    /**
     * Calculates the shipped quantity for the given sale item.
     *
     * @param Common\SaleItemInterface   $saleItem
     * @param Shipment\ShipmentInterface $ignore
     *
     * @return float
     */
    public function calculateShippedQuantity(Common\SaleItemInterface $saleItem, Shipment\ShipmentInterface $ignore = null);

    /**
     * Calculates the returned quantity for the given sale item.
     *
     * @param Common\SaleItemInterface   $saleItem
     * @param Shipment\ShipmentInterface $ignore
     *
     * @return float
     */
    public function calculateReturnedQuantity(Common\SaleItemInterface $saleItem, Shipment\ShipmentInterface $ignore = null);

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
    public function buildShipmentQuantityMap(Shipment\ShipmentSubjectInterface $subject);
}

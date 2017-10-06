<?php

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Interface QuantityCalculatorInterface
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuantityCalculatorInterface
{
    /**
     * Calculate the shipment item available quantity.
     *
     * @param ShipmentItemInterface $item
     *
     * @return float
     */
    public function calculateAvailableQuantity(ShipmentItemInterface $item);

    /**
     * Calculates the shipment item shippable quantity.
     *
     * @param ShipmentItemInterface $item
     *
     * @return float
     */
    public function calculateShippableQuantity(ShipmentItemInterface $item);

    /**
     * Calculates the shipment item returnable quantity.
     *
     * @param ShipmentItemInterface $item
     *
     * @return float
     */
    public function calculateReturnableQuantity(ShipmentItemInterface $item);

    /**
     * Calculates the shipped quantity for the given sale item.
     *
     * @param SaleItemInterface $saleItem
     *
     * @return float
     */
    public function calculateShippedQuantity(SaleItemInterface $saleItem);

    /**
     * Calculates the returned quantity for the given sale item.
     *
     * @param SaleItemInterface $saleItem
     *
     * @return float
     */
    public function calculateReturnedQuantity(SaleItemInterface $saleItem);
}

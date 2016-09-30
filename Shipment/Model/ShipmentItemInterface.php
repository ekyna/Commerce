<?php


namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ShipmentItemInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentItemInterface extends ResourceInterface
{
    /**
     * Returns the shipment.
     *
     * @return ShipmentInterface
     */
    public function getShipment();

    /**
     * Sets the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return $this|ShipmentItemInterface
     */
    public function setShipment(ShipmentInterface $shipment = null);

    /**
     * Returns the sale item.
     *
     * @return SaleItemInterface
     */
    public function getSaleItem();

    /**
     * Sets the sale item.
     *
     * @param SaleItemInterface $saleItem
     *
     * @return $this|ShipmentItemInterface
     */
    public function setSaleItem(SaleItemInterface $saleItem);

    /**
     * Returns the shipped quantity.
     *
     * @return float
     */
    public function getShippedQuantity();

    /**
     * Sets the shipped quantity.
     *
     * @param float $quantity
     *
     * @return $this|ShipmentItemInterface
     */
    public function setShippedQuantity($quantity);

    /**
     * Returns the debited quantity.
     *
     * @return float
     */
    public function getDebitedQuantity();

    /**
     * Sets the debited quantity.
     *
     * @param float $quantity
     *
     * @return $this|ShipmentItemInterface
     */
    public function setDebitedQuantity($quantity);
}

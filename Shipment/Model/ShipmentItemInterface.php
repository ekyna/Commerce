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
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return $this|ShipmentItemInterface
     */
    public function setQuantity($quantity);

    /**
     * Returns the children shipment items.
     *
     * @return \Doctrine\Common\Collections\Collection|ShipmentItemInterface[]
     */
    public function getChildren();

    /**
     * Clears the children.
     *
     * @return $this|ShipmentItemInterface
     */
    public function clearChildren();

    /**
     * Returns the expected.
     *
     * @return float
     */
    public function getExpected();

    /**
     * Sets the expected.
     *
     * @param float $expected
     *
     * @return $this|ShipmentItemInterface
     */
    public function setExpected($expected);

    /**
     * Returns the available.
     *
     * @return float
     */
    public function getAvailable();

    /**
     * Sets the available.
     *
     * @param float $available
     *
     * @return $this|ShipmentItemInterface
     */
    public function setAvailable($available);
}

<?php

namespace Ekyna\Component\Commerce\Credit\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface CreditItemInterface
 * @package Ekyna\Component\Commerce\Credit\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CreditItemInterface extends ResourceInterface
{
    /**
     * Returns the credit.
     *
     * @return CreditInterface
     */
    public function getCredit();

    /**
     * Sets the credit.
     *
     * @param CreditInterface $credit
     *
     * @return $this|CreditItemInterface
     */
    public function setCredit(CreditInterface $credit = null);

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
     * @return $this|CreditItemInterface
     */
    public function setSaleItem(SaleItemInterface $saleItem);

    /**
     * Returns the shipment item.
     *
     * @return ShipmentItemInterface|null
     */
    public function getShipmentItem();

    /**
     * Sets the shipment item.
     *
     * @param ShipmentItemInterface $shipmentItem
     *
     * @return $this|CreditItemInterface
     */
    public function setShipmentItem(ShipmentItemInterface $shipmentItem = null);

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
     * @return $this|CreditItemInterface
     */
    public function setQuantity($quantity);
}

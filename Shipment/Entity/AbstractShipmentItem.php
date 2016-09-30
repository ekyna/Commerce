<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Class ShipmentItem
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractShipmentItem implements ShipmentItemInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ShipmentInterface
     */
    protected $shipment;

    /**
     * @var SaleItemInterface
     */
    protected $saleItem;

    /**
     * @var float
     */
    protected $shippedQuantity = 0;

    /**
     * @var float
     */
    protected $debitedQuantity = 0;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * @inheritdoc
     */
    public function setShipment(ShipmentInterface $shipment = null)
    {
        if ($this->shipment && $this->shipment != $shipment) {
            $this->shipment->removeItem($this);
        }

        $this->shipment = $shipment;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSaleItem()
    {
        return $this->saleItem;
    }

    /**
     * @inheritdoc
     */
    public function setSaleItem(SaleItemInterface $saleItem)
    {
        $this->saleItem = $saleItem;

        return $this;
    }

    /**
     * Returns the shipped quantity.
     *
     * @return float
     */
    public function getShippedQuantity()
    {
        return $this->shippedQuantity;
    }

    /**
     * Sets the shipped quantity.
     *
     * @param float $quantity
     *
     * @return AbstractShipmentItem
     */
    public function setShippedQuantity($quantity)
    {
        $this->shippedQuantity = $quantity;

        return $this;
    }

    /**
     * Returns the debited quantity.
     *
     * @return float
     */
    public function getDebitedQuantity()
    {
        return $this->debitedQuantity;
    }

    /**
     * Sets the debited quantity.
     *
     * @param float $quantity
     *
     * @return AbstractShipmentItem
     */
    public function setDebitedQuantity($quantity)
    {
        $this->debitedQuantity = $quantity;

        return $this;
    }
}

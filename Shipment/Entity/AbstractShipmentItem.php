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
abstract class AbstractShipmentItem implements ShipmentItemInterface
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
     * @var float
     */
    protected $quantity = 0;


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
        if ($this->shipment != $shipment) {
            if ($this->shipment) {
                $this->shipment->removeItem($this);
            }

            $this->shipment = $shipment;

            if ($this->shipment) {
                $this->shipment->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
}

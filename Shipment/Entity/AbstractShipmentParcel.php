<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class AbstractShipmentParcel
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentParcel implements Model\ShipmentParcelInterface
{
    use Model\ShipmentDataTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\ShipmentInterface
     */
    protected $shipment;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeShipmentData();
    }

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
    public function setShipment(Model\ShipmentInterface $shipment = null)
    {
        if ($this->shipment !== $shipment) {
            $previous = $this->shipment;
            $this->shipment = $shipment;

            if ($previous) {
                $previous->removeParcel($this);
            }

            if ($this->shipment) {
                $this->shipment->addParcel($this);
            }
        }

        return $this;
    }
}

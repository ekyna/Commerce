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
    public function getId(): ?int
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
            if ($previous = $this->shipment) {
                $this->shipment = null;
                $previous->removeParcel($this);
            }

            if ($this->shipment = $shipment) {
                $this->shipment->addParcel($this);
            }
        }

        return $this;
    }
}

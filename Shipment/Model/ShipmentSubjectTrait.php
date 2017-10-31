<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait ShipmentSubjectTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ShipmentSubjectTrait
{
    /**
     * @var string
     */
    protected $shipmentState;

    /**
     * @var \Doctrine\Common\Collections\Collection|ShipmentInterface[]
     */
    protected $shipments;


    /**
     * Initializes the shipments.
     */
    protected function initializeShipmentSubject()
    {
        $this->shipmentState = ShipmentStates::STATE_NONE;
        $this->shipments = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function setShipmentState($state)
    {
        $this->shipmentState = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShipmentState()
    {
        return $this->shipmentState;
    }

    /**
     * Returns whether the order has shipments or not.
     *
     * @return bool
     */
    public function hasShipments()
    {
        return 0 < $this->shipments->count();
    }

    /**
     * Returns the shipments.
     *
     * @return \Doctrine\Common\Collections\Collection|ShipmentInterface[]
     */
    public function getShipments()
    {
        return $this->shipments;
    }
}

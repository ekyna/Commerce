<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

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

    /**
     * Returns the first shipment date.
     *
     * @return \DateTime|null
     */
    public function getShippedAt()
    {
        if (0 == $this->shipments->count()) {
            return null;
        }

        $criteria = Criteria::create();
        $criteria
            ->andWhere(Criteria::expr()->eq('return', false))
            ->andWhere(Criteria::expr()->in('state', ShipmentStates::getShippedStates()))
            ->orderBy(['createdAt' => Criteria::ASC]);

        /** @var ArrayCollection $shipments */
        $shipments = $this->shipments;
        $shipments = $shipments->matching($criteria);

        /** @var ShipmentInterface $shipment */
        if (false !== $shipment = $shipments->first()) {
            return $shipment->getCreatedAt();
        }

        return null;
    }
}

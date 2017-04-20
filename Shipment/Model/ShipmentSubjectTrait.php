<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

/**
 * Trait ShipmentSubjectTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ShipmentSubjectTrait
{
    protected string $shipmentState;

    /** @var Collection|ShipmentInterface[] */
    protected Collection $shipments;


    protected function initializeShipmentSubject(): void
    {
        $this->shipmentState = ShipmentStates::STATE_NONE;
        $this->shipments = new ArrayCollection();
    }

    /**
     * @return $this|ShipmentSubjectInterface
     */
    public function setShipmentState(string $state): ShipmentSubjectInterface
    {
        $this->shipmentState = $state;

        return $this;
    }

    public function getShipmentState(): string
    {
        return $this->shipmentState;
    }

    public function hasShipments(): bool
    {
        return 0 < $this->shipments->count();
    }

    /**
     * @param bool $filter TRUE for shipments, FALSE for returns, NULL for all
     *
     * @return Collection|ShipmentInterface[]
     */
    public function getShipments(bool $filter = null): Collection
    {
        if (null === $filter) {
            return $this->shipments;
        }

        return $this->shipments->filter(function(ShipmentInterface $shipment) use ($filter) {
            return $filter xor $shipment->isReturn();
        });
    }

    /**
     * @param bool $latest Whether to return the last shipment date instead of the first
     */
    public function getShippedAt(bool $latest = false): ?DateTimeInterface
    {
        if (0 == $this->shipments->count()) {
            return null;
        }

        $criteria = Criteria::create();
        $criteria
            ->andWhere(Criteria::expr()->eq('return', false))
            ->andWhere(Criteria::expr()->in('state', [ShipmentStates::STATE_READY, ShipmentStates::STATE_SHIPPED]))
            ->orderBy(['createdAt' => $latest ? Criteria::DESC : Criteria::ASC]);

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

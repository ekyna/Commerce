<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Class ShipmentSubjectInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentSubjectInterface extends ShippableInterface
{
    public function getShipmentState(): string;

    public function setShipmentState(string $state): ShipmentSubjectInterface;

    public function hasShipments(): bool;

    /**
     * @param bool $filter TRUE for shipments, FALSE for returns, NULL for all
     *
     * @return Collection|ShipmentInterface[]
     */
    public function getShipments(bool $filter = null): Collection;

    public function hasShipment(ShipmentInterface $shipment): bool;

    public function addShipment(ShipmentInterface $shipment): ShipmentSubjectInterface;

    public function removeShipment(ShipmentInterface $shipment): ShipmentSubjectInterface;

    /**
     * @param bool $latest Whether to return the last shipment date instead of the first
     */
    public function getShippedAt(bool $latest = false) :?DateTimeInterface;
}

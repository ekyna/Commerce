<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;

/**
 * Interface ShipmentDataInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentDataInterface
{
    public function getWeight(): ?Decimal;

    public function setWeight(?Decimal $weight): ShipmentDataInterface;

    public function getValorization(): ?Decimal;

    public function setValorization(?Decimal $valorization): ShipmentDataInterface;

    public function getTrackingNumber(): ?string;

    public function setTrackingNumber(?string $number): ShipmentDataInterface;

    /**
     * @return Collection<int, ShipmentLabelInterface>
     */
    public function getLabels(): Collection;

    /**
     * Returns whether the shipment/parcel has label(s).
     */
    public function hasLabels(): bool;

    /**
     * Returns whether the shipment/parcel has the given label.
     */
    public function hasLabel(ShipmentLabelInterface $label): bool;

    public function addLabel(ShipmentLabelInterface $label): ShipmentDataInterface;

    public function removeLabel(ShipmentLabelInterface $label): ShipmentDataInterface;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Trait ShipmentDataTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ShipmentDataTrait
{
    protected ?Decimal $weight         = null;
    protected ?Decimal $valorization   = null;
    protected ?string  $trackingNumber = null;
    protected ?array   $resultData     = [];
    /** @var Collection<int, ShipmentLabelInterface> */
    protected Collection $labels;


    /**
     * Initializes the shipment data.
     */
    protected function initializeShipmentData(): void
    {
        $this->labels = new ArrayCollection();
    }

    public function getWeight(): ?Decimal
    {
        return $this->weight;
    }

    public function setWeight(?Decimal $weight): ShipmentDataInterface
    {
        $this->weight = $weight;

        return $this;
    }

    public function getValorization(): ?Decimal
    {
        return $this->valorization;
    }

    public function setValorization(?Decimal $valorization): ShipmentDataInterface
    {
        $this->valorization = $valorization;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $number): ShipmentDataInterface
    {
        $this->trackingNumber = $number;

        return $this;
    }

    public function getResultData(): ?array
    {
        return $this->resultData;
    }

    public function setResultData(?array $resultData): ShipmentDataInterface
    {
        $this->resultData = $resultData;

        return $this;
    }

    public function hasLabels(): bool
    {
        return 0 < $this->labels->count();
    }

    /**
     * @return Collection<int, ShipmentLabelInterface>
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    public function hasLabel(ShipmentLabelInterface $label): bool
    {
        return $this->labels->contains($label);
    }

    public function addLabel(ShipmentLabelInterface $label): ShipmentDataInterface
    {
        if (!$this->hasLabel($label)) {
            $this->labels->add($label);

            if ($this instanceof ShipmentInterface) {
                $label->setShipment($this)->setParcel(null);
            } else {
                $label->setParcel($this)->setShipment(null);
            }
        }

        return $this;
    }

    public function removeLabel(ShipmentLabelInterface $label): ShipmentDataInterface
    {
        if ($this->hasLabel($label)) {
            $this->labels->removeElement($label);

            if ($this instanceof ShipmentInterface) {
                $label->setShipment(null);
            } else {
                $label->setParcel(null);
            }
        }

        return $this;
    }
}

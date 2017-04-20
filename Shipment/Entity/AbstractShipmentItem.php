<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class ShipmentItem
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentItem implements Model\ShipmentItemInterface
{
    protected ?int                     $id       = null;
    protected ?Model\ShipmentInterface $shipment = null;
    protected Decimal                  $quantity;
    /** @var Collection|array<static> */
    protected Collection $children;

    /* Non-mapped fields */
    protected ?Decimal $expected  = null;
    protected ?Decimal $available = null;

    public function __construct()
    {
        $this->clearChildren();

        $this->quantity = new Decimal(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShipment(): ?Model\ShipmentInterface
    {
        return $this->shipment;
    }

    public function setShipment(?Model\ShipmentInterface $shipment): Model\ShipmentItemInterface
    {
        if ($this->shipment === $shipment) {
            return $this;
        }

        if ($previous = $this->shipment) {
            $this->shipment = null;
            $previous->removeItem($this);
        }

        if ($this->shipment = $shipment) {
            $this->shipment->addItem($this);
        }

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): Model\ShipmentItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function setChildren(array $children): Model\ShipmentItemInterface
    {
        $this->clearChildren();

        foreach ($children as $child) {
            $this->children->add($child);
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function clearChildren(): Model\ShipmentItemInterface
    {
        $this->children = new ArrayCollection();

        return $this;
    }

    public function getExpected(): ?Decimal
    {
        return $this->expected;
    }

    public function setExpected(?Decimal $expected): Model\ShipmentItemInterface
    {
        $this->expected = $expected;

        return $this;
    }

    public function getAvailable(): ?Decimal
    {
        return $this->available;
    }

    public function setAvailable(?Decimal $available): Model\ShipmentItemInterface
    {
        $this->available = $available;

        return $this;
    }
}

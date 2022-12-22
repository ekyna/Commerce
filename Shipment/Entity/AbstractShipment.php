<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractShipment
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipment extends AbstractResource implements Shipment\ShipmentInterface
{
    use Common\NumberSubjectTrait;
    use Common\StateSubjectTrait;
    use Shipment\ShipmentDataTrait;
    use TimestampableTrait;

    protected ?Shipment\ShipmentMethodInterface $method          = null;
    protected bool                              $autoInvoice;
    protected bool                              $return;
    protected ?string                           $description     = null;
    protected ?array                            $gatewayData     = null;
    protected ?DateTimeInterface                $shippedAt       = null;
    protected ?DateTimeInterface                $completedAt     = null;
    protected ?array                            $senderAddress   = null;
    protected ?array                            $receiverAddress = null;
    protected ?Shipment\RelayPointInterface     $relayPoint      = null;
    /** @var Collection<int, Shipment\ShipmentItemInterface> */
    protected Collection $items;
    /** @var Collection<int, Shipment\ShipmentParcelInterface> */
    protected Collection $parcels;

    public function __construct()
    {
        $this->state = Shipment\ShipmentStates::STATE_NEW;
        $this->items = new ArrayCollection();
        $this->parcels = new ArrayCollection();
        $this->return = false;
        $this->autoInvoice = true;

        $this->initializeShipmentData();
    }

    public function __toString(): string
    {
        return $this->number ?: 'New shipment rule';
    }

    public function getMethod(): ?Shipment\ShipmentMethodInterface
    {
        return $this->method;
    }

    public function setMethod(?Shipment\ShipmentMethodInterface $method): Shipment\ShipmentInterface
    {
        $this->method = $method;

        return $this;
    }

    public function isAutoInvoice(): bool
    {
        return $this->autoInvoice;
    }

    public function setAutoInvoice(bool $auto): Shipment\ShipmentInterface
    {
        $this->autoInvoice = $auto;

        return $this;
    }

    public function hasItems(): bool
    {
        return 0 < $this->items->count();
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function hasItem(Shipment\ShipmentItemInterface $item): bool
    {
        return $this->items->contains($item);
    }

    public function addItem(Shipment\ShipmentItemInterface $item): Shipment\ShipmentInterface
    {
        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setShipment($this);
        }

        return $this;
    }

    public function removeItem(Shipment\ShipmentItemInterface $item): Shipment\ShipmentInterface
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setShipment(null);
        }

        return $this;
    }

    public function hasParcels(): bool
    {
        return 0 < $this->parcels->count();
    }

    public function getParcels(): Collection
    {
        return $this->parcels;
    }

    public function hasParcel(Shipment\ShipmentParcelInterface $parcel): bool
    {
        return $this->parcels->contains($parcel);
    }

    public function addParcel(Shipment\ShipmentParcelInterface $parcel): Shipment\ShipmentInterface
    {
        if (!$this->hasParcel($parcel)) {
            $this->parcels->add($parcel);
            $parcel->setShipment($this);
        }

        return $this;
    }

    public function removeParcel(Shipment\ShipmentParcelInterface $parcel): Shipment\ShipmentInterface
    {
        if ($this->hasParcel($parcel)) {
            $this->parcels->removeElement($parcel);
            $parcel->setShipment(null);
        }

        return $this;
    }

    public function isReturn(): bool
    {
        return $this->return;
    }

    public function setReturn(bool $return): Shipment\ShipmentInterface
    {
        $this->return = $return;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Shipment\ShipmentInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getPlatformName(): string
    {
        if ($this->method) {
            return $this->method->getPlatformName();
        }

        throw new LogicException('Shipment method is not set.');
    }

    public function getGatewayName(): string
    {
        if ($this->method) {
            return $this->method->getGatewayName();
        }

        throw new LogicException('Shipment method is not set.');
    }

    public function getGatewayData(): ?array
    {
        return $this->gatewayData;
    }

    public function setGatewayData(?array $data): Shipment\ShipmentInterface
    {
        $this->gatewayData = $data;

        return $this;
    }

    public function getShippedAt(): ?DateTimeInterface
    {
        return $this->shippedAt;
    }

    public function setShippedAt(?DateTimeInterface $date): Shipment\ShipmentInterface
    {
        $this->shippedAt = $date;

        return $this;
    }

    public function getCompletedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?DateTimeInterface $date): Shipment\ShipmentInterface
    {
        $this->completedAt = $date;

        return $this;
    }

    public function getSenderAddress(): ?array
    {
        return $this->senderAddress;
    }

    public function setSenderAddress(?array $data): Shipment\ShipmentInterface
    {
        $this->senderAddress = empty($data) ? null : $data;

        return $this;
    }

    public function getReceiverAddress(): ?array
    {
        return $this->receiverAddress;
    }

    public function setReceiverAddress(?array $data): Shipment\ShipmentInterface
    {
        $this->receiverAddress = empty($data) ? null : $data;

        return $this;
    }

    public function getRelayPoint(): ?Shipment\RelayPointInterface
    {
        return $this->relayPoint;
    }

    public function setRelayPoint(?Shipment\RelayPointInterface $relayPoint): Shipment\ShipmentInterface
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }

    public function isEmpty(): bool
    {
        foreach ($this->items as $item) {
            if (0 < $item->getQuantity()) {
                return false;
            }
        }

        return true;
    }

    public function isPartial(): bool
    {
        $coveredIds = [];

        // For each shipment items
        foreach ($this->items as $item) {
            // Retain sale item id
            $coveredIds[] = $item->getSaleItem()->getId();
            // If shipment item quantity does not equal sale item total quantity
            if (!$item->getQuantity()->equals($item->getSaleItem()->getTotalQuantity())) {
                // Shipment is partial
                return true;
            }
        }

        $sale = $this->getSale();
        // For each sale items
        foreach ($sale->getItems() as $saleItem) {
            // IF sale item id is not in retained sale items ids
            if (!$this->isSaleItemCovered($saleItem, $coveredIds)) {
                // Shipment is partial
                return true;
            }
        }

        // Shipment is not partial
        return false;
    }

    /**
     * Returns whether the given sale item is covered by this shipment.
     */
    private function isSaleItemCovered(Common\SaleItemInterface $saleItem, array $coveredIds): bool
    {
        // Skip compound with only public children
        if ($saleItem->isCompound() && !$saleItem->hasPrivateChildren()) {
            return true;
        }

        if (!in_array($saleItem->getId(), $coveredIds, true)) {
            return false;
        }

        foreach ($saleItem->getChildren() as $child) {
            if (!$this->isSaleItemCovered($child, $coveredIds)) {
                return false;
            }
        }

        return true;
    }
}

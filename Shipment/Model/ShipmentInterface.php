<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Model as Resource;

/**
 * Interface ShipmentInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentInterface extends
    Resource\ResourceInterface,
    Resource\TimestampableInterface,
    Resource\LocalizedInterface,
    Common\NumberSubjectInterface,
    Common\StateSubjectInterface,
    ShipmentDataInterface
{
    /**
     * @return Common\SaleInterface|ShipmentSubjectInterface|null
     */
    public function getSale(): ?Common\SaleInterface;

    public function getInvoice(): ?InvoiceInterface;

    public function setInvoice(?InvoiceInterface $invoice): ShipmentInterface;

    public function getMethod(): ?ShipmentMethodInterface;

    public function setMethod(?ShipmentMethodInterface $method): ShipmentInterface;

    /**
     * Returns whether the shipment has at least one item or not.
     */
    public function hasItems(): bool;

    /**
     * @return Collection<int, ShipmentItemInterface>
     */
    public function getItems(): Collection;

    public function hasItem(ShipmentItemInterface $item): bool;

    public function addItem(ShipmentItemInterface $item): ShipmentInterface;

    public function removeItem(ShipmentItemInterface $item): ShipmentInterface;

    /**
     * Returns whether the shipment has at least one parcel or not.
     */
    public function hasParcels(): bool;

    /**
     * @return Collection<int, ShipmentParcelInterface>
     */
    public function getParcels(): Collection;

    /**
     * Returns whether the shipment has the parcel or not.
     */
    public function hasParcel(ShipmentParcelInterface $parcel): bool;

    public function addParcel(ShipmentParcelInterface $parcel): ShipmentInterface;

    public function removeParcel(ShipmentParcelInterface $parcel): ShipmentInterface;

    /**
     * Returns whether an equivalent invoice should be generated automatically.
     */
    public function isAutoInvoice(): bool;

    /**
     * Sets whether an equivalent invoice should be generated automatically.
     */
    public function setAutoInvoice(bool $auto): ShipmentInterface;

    /**
     * Returns whether the shipment is a return.
     */
    public function isReturn();

    /**
     * Sets whether the shipment is a return.
     */
    public function setReturn(bool $return): ShipmentInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): ShipmentInterface;

    public function getPlatformName(): string;

    public function getGatewayName(): string;

    public function getGatewayData(): ?array;

    public function setGatewayData(?array $data): ShipmentInterface;

    /**
     * Returns the 'shipped at' date.
     */
    public function getShippedAt(): ?DateTimeInterface;

    /**
     * Sets the 'shipped at' date.
     */
    public function setShippedAt(?DateTimeInterface $date): ShipmentInterface;

    /**
     * Returns the 'completed at' date.
     */
    public function getCompletedAt(): ?DateTimeInterface;

    /**
     * Sets the 'completed at' date.
     */
    public function setCompletedAt(?DateTimeInterface $date): ShipmentInterface;

    public function getSenderAddress(): ?array;

    public function setSenderAddress(?array $data): ShipmentInterface;

    public function getReceiverAddress(): ?array;

    public function setReceiverAddress(?array $data): ShipmentInterface;

    public function getRelayPoint(): ?RelayPointInterface;

    public function setRelayPoint(?RelayPointInterface $relayPoint): ShipmentInterface;

    /**
     * Returns whether the shipment is empty
     * (do not have at least one item with quantity greater than zero).
     *
     * @TODO Move to ShipmentUtil
     */
    public function isEmpty(): bool;

    /**
     * Returns whether this shipment is a partial one.
     *
     * @TODO Move to ShipmentUtil
     */
    public function isPartial(): bool;
}

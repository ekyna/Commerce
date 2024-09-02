<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Class SupplierOrderInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderInterface extends
    ResourceModel\ResourceInterface,
    Common\NumberSubjectInterface,
    Common\ExchangeSubjectInterface,
    Common\StateSubjectInterface,
    ResourceModel\TimestampableInterface,
    ResourceModel\LocalizedInterface
{
    public function getSupplier(): ?SupplierInterface;

    public function setSupplier(?SupplierInterface $supplier): SupplierOrderInterface;

    public function getCarrier(): ?SupplierCarrierInterface;

    public function setCarrier(?SupplierCarrierInterface $carrier): SupplierOrderInterface;

    public function getWarehouse(): ?WarehouseInterface;

    public function setWarehouse(?WarehouseInterface $warehouse): SupplierOrderInterface;

    /**
     * Returns whether the supplier order has at least one item.
     */
    public function hasItems(): bool;

    /**
     * Returns whether the supplier order has the item or not.
     */
    public function hasItem(SupplierOrderItemInterface $item): bool;

    public function addItem(SupplierOrderItemInterface $item): SupplierOrderInterface;

    public function removeItem(SupplierOrderItemInterface $item): SupplierOrderInterface;

    /**
     * @return Collection<int, SupplierOrderItemInterface>
     */
    public function getItems(): Collection;

    /**
     * Returns whether the supplier order has at least one delivery.
     */
    public function hasDeliveries(): bool;

    /**
     * Returns whether the supplier order has the delivery or not.
     */
    public function hasDelivery(SupplierDeliveryInterface $delivery): bool;

    public function addDelivery(SupplierDeliveryInterface $delivery): SupplierOrderInterface;

    public function removeDelivery(SupplierDeliveryInterface $delivery): SupplierOrderInterface;

    /**
     * @return Collection<int, SupplierDeliveryInterface>
     */
    public function getDeliveries(): Collection;

    /**
     * Returns whether the order has attachments or not, optionally filtered by type.
     */
    public function hasAttachments(string $type = null): bool;

    /**
     * Returns whether the order has the attachment or not.
     */
    public function hasAttachment(SupplierOrderAttachmentInterface $attachment): bool;

    public function addAttachment(SupplierOrderAttachmentInterface $attachment): SupplierOrderInterface;

    public function removeAttachment(SupplierOrderAttachmentInterface $attachment): SupplierOrderInterface;

    /**
     * Returns the supplier attachments.
     *
     * @return Collection<int, SupplierOrderAttachmentInterface>
     */
    public function getSupplierAttachments(): Collection;

    /**
     * Returns the attachments.
     *
     * @return Collection<int, SupplierOrderAttachmentInterface>
     */
    public function getAttachments(): Collection;

    public function hasPayments(bool $toForwarder = null): bool;

    public function hasPayment(SupplierPaymentInterface $payment): bool;

    public function addPayment(SupplierPaymentInterface $payment): SupplierOrderInterface;

    public function removePayment(SupplierPaymentInterface $payment): SupplierOrderInterface;

    /**
     * Returns the payments.
     *
     * @return Collection<int, SupplierPaymentInterface>
     */
    public function getPayments(bool $toForwarder = null): Collection;

    public function getShippingCost(): Decimal;

    public function setShippingCost(Decimal $amount): SupplierOrderInterface;

    public function getDiscountTotal(): Decimal;

    public function setDiscountTotal(Decimal $amount): SupplierOrderInterface;

    public function getTaxTotal(): Decimal;

    public function setTaxTotal(Decimal $amount): SupplierOrderInterface;

    public function getPaymentTotal(): Decimal;

    public function setPaymentTotal(Decimal $amount): SupplierOrderInterface;

    public function getPaymentPaidTotal(): Decimal;

    public function setPaymentPaidTotal(Decimal $amount): SupplierOrderInterface;

    public function getPaymentDate(): ?DateTimeInterface;

    public function setPaymentDate(?DateTimeInterface $date): SupplierOrderInterface;

    public function getPaymentDueDate(): ?DateTimeInterface;

    public function setPaymentDueDate(?DateTimeInterface $date): SupplierOrderInterface;

    public function isReverseCharge(): bool;

    public function setReverseCharge(bool $reverseCharge): SupplierOrderInterface;

    public function getCustomsTax(): Decimal;

    public function setCustomsTax(Decimal $amount): SupplierOrderInterface;

    public function getCustomsVat(): Decimal;

    public function setCustomsVat(Decimal $amount): SupplierOrderInterface;

    public function getForwarderFee(): Decimal;

    public function setForwarderFee(Decimal $amount): SupplierOrderInterface;

    public function getForwarderTotal(): Decimal;

    public function setForwarderTotal(Decimal $amount): SupplierOrderInterface;

    public function getForwarderPaidTotal(): Decimal;

    public function setForwarderPaidTotal(Decimal $amount): SupplierOrderInterface;

    /**
     * Returns the forwarder payment date.
     */
    public function getForwarderDate(): ?DateTimeInterface;

    /**
     * Sets the forwarder payment date.
     */
    public function setForwarderDate(?DateTimeInterface $date): SupplierOrderInterface;

    /**
     * Returns the forwarder due date.
     */
    public function getForwarderDueDate(): ?DateTimeInterface;

    /**
     * Sets the forwarder due date.
     */
    public function setForwarderDueDate(?DateTimeInterface $date): SupplierOrderInterface;

    /**
     * Returns the estimated date of arrival.
     */
    public function getEstimatedDateOfArrival(): ?DateTimeInterface;

    /**
     * Sets the estimated date of arrival.
     */
    public function setEstimatedDateOfArrival(?DateTimeInterface $date): SupplierOrderInterface;

    /**
     * Returns the tracking urls.
     */
    public function getTrackingUrls(): ?array;

    /**
     * Sets the tracking urls.
     */
    public function setTrackingUrls(?array $urls): SupplierOrderInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): SupplierOrderInterface;

    /**
     * Returns the 'ordered at' date.
     */
    public function getOrderedAt(): ?DateTimeInterface;

    /**
     * Sets the 'ordered at' date.
     */
    public function setOrderedAt(?DateTimeInterface $date): SupplierOrderInterface;

    /**
     * Returns the "completed at" date.
     */
    public function getCompletedAt(): ?DateTimeInterface;

    /**
     * Sets the "completed at" date.
     */
    public function setCompletedAt(?DateTimeInterface $date): SupplierOrderInterface;
}

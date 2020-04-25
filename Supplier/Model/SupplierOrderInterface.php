<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use DateTime;
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
    /**
     * Returns the supplier.
     *
     * @return SupplierInterface
     */
    public function getSupplier(): ?SupplierInterface;

    /**
     * Sets the supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return $this|SupplierOrderInterface
     */
    public function setSupplier(SupplierInterface $supplier): SupplierOrderInterface;

    /**
     * Returns the carrier.
     *
     * @return SupplierCarrierInterface
     */
    public function getCarrier(): ?SupplierCarrierInterface;

    /**
     * Sets the carrier.
     *
     * @param SupplierCarrierInterface $carrier
     *
     * @return $this|SupplierOrderInterface
     */
    public function setCarrier(SupplierCarrierInterface $carrier = null): SupplierOrderInterface;

    /**
     * Returns the warehouse.
     *
     * @return WarehouseInterface
     */
    public function getWarehouse(): ?WarehouseInterface;

    /**
     * Sets the warehouse.
     *
     * @param WarehouseInterface $warehouse
     *
     * @return SupplierOrderInterface
     */
    public function setWarehouse(WarehouseInterface $warehouse): SupplierOrderInterface;

    /**
     * Returns whether or not the supplier order has at least one item.
     *
     * @return bool
     */
    public function hasItems(): bool;

    /**
     * Returns whether the supplier order has the item or not.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return bool
     */
    public function hasItem(SupplierOrderItemInterface $item): bool;

    /**
     * Adds the item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return $this|SupplierOrderInterface
     */
    public function addItem(SupplierOrderItemInterface $item): SupplierOrderInterface;

    /**
     * Removes the item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return $this|SupplierOrderInterface
     */
    public function removeItem(SupplierOrderItemInterface $item): SupplierOrderInterface;

    /**
     * Returns the items.
     *
     * @return Collection|SupplierOrderItemInterface[]
     */
    public function getItems(): Collection;

    /**
     * Returns whether or not the supplier order has at least one delivery.
     *
     * @return bool
     */
    public function hasDeliveries(): bool;

    /**
     * Returns whether the supplier order has the delivery or not.
     *
     * @param SupplierDeliveryInterface $delivery
     *
     * @return bool
     */
    public function hasDelivery(SupplierDeliveryInterface $delivery): bool;

    /**
     * Adds the delivery.
     *
     * @param SupplierDeliveryInterface $delivery
     *
     * @return $this|SupplierOrderInterface
     */
    public function addDelivery(SupplierDeliveryInterface $delivery): SupplierOrderInterface;

    /**
     * Removes the delivery.
     *
     * @param SupplierDeliveryInterface $delivery
     *
     * @return $this|SupplierOrderInterface
     */
    public function removeDelivery(SupplierDeliveryInterface $delivery): SupplierOrderInterface;

    /**
     * Returns the deliveries.
     *
     * @return Collection|SupplierDeliveryInterface[]
     */
    public function getDeliveries(): Collection;

    /**
     * Returns whether the order has attachments or not, optionally filtered by type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasAttachments(string $type = null): bool;

    /**
     * Returns whether the order has the attachment or not.
     *
     * @param SupplierOrderAttachmentInterface $attachment
     *
     * @return bool
     */
    public function hasAttachment(SupplierOrderAttachmentInterface $attachment): bool;

    /**
     * Adds the attachment.
     *
     * @param SupplierOrderAttachmentInterface $attachment
     *
     * @return $this|SupplierOrderInterface
     */
    public function addAttachment(SupplierOrderAttachmentInterface $attachment): SupplierOrderInterface;

    /**
     * Removes the attachment.
     *
     * @param SupplierOrderAttachmentInterface $attachment
     *
     * @return $this|SupplierOrderInterface
     */
    public function removeAttachment(SupplierOrderAttachmentInterface $attachment): SupplierOrderInterface;

    /**
     * Returns the supplier attachments.
     *
     * @return Collection|SupplierOrderAttachmentInterface[]
     */
    public function getSupplierAttachments(): Collection;

    /**
     * Returns the attachments.
     *
     * @return Collection|SupplierOrderAttachmentInterface[]
     */
    public function getAttachments(): Collection;

    /**
     * Returns the shipping cost.
     *
     * @return float
     */
    public function getShippingCost(): float;

    /**
     * Sets the shipping cost.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setShippingCost(float $amount): SupplierOrderInterface;

    /**
     * Returns the discount total.
     *
     * @return float
     */
    public function getDiscountTotal(): float;

    /**
     * Sets the discount total.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setDiscountTotal(float $amount): SupplierOrderInterface;

    /**
     * Returns the tax total.
     *
     * @return float
     */
    public function getTaxTotal(): float;

    /**
     * Sets the tax total.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setTaxTotal(float $amount): SupplierOrderInterface;

    /**
     * Returns the payment total.
     *
     * @return float
     */
    public function getPaymentTotal(): float;

    /**
     * Sets the payment total.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setPaymentTotal(float $amount): SupplierOrderInterface;

    /**
     * Returns the payment date.
     *
     * @return DateTime
     */
    public function getPaymentDate(): ?DateTime;

    /**
     * Sets the payment date.
     *
     * @param DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setPaymentDate(DateTime $date = null): SupplierOrderInterface;

    /**
     * Returns the payment due date.
     *
     * @return DateTime
     */
    public function getPaymentDueDate(): ?DateTime;

    /**
     * Sets the payment due date.
     *
     * @param DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setPaymentDueDate(DateTime $date = null): SupplierOrderInterface;

    /**
     * Returns the customs tax.
     *
     * @return float
     */
    public function getCustomsTax(): float;

    /**
     * Sets the customs tax.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setCustomsTax(float $amount): SupplierOrderInterface;

    /**
     * Returns the customs VAT amount.
     *
     * @return float
     */
    public function getCustomsVat(): float;

    /**
     * Sets the customs VAT amount.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setCustomsVat(float $amount): SupplierOrderInterface;

    /**
     * Returns the "forwarder fee" amount.
     *
     * @return float
     */
    public function getForwarderFee(): float;

    /**
     * Sets the "forwarder fee" amount.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setForwarderFee(float $amount): SupplierOrderInterface;

    /**
     * Returns the forwarder total.
     *
     * @return float
     */
    public function getForwarderTotal(): float;

    /**
     * Sets the forwarder total.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setForwarderTotal(float $amount): SupplierOrderInterface;

    /**
     * Returns the forwarder payment date.
     *
     * @return DateTime
     */
    public function getForwarderDate(): ?DateTime;

    /**
     * Sets the forwarder payment date.
     *
     * @param DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setForwarderDate(DateTime $date = null): SupplierOrderInterface;

    /**
     * Returns the forwarder due date.
     *
     * @return DateTime
     */
    public function getForwarderDueDate(): ?DateTime;

    /**
     * Sets the forwarder due date.
     *
     * @param DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setForwarderDueDate(DateTime $date = null): SupplierOrderInterface;

    /**
     * Returns the estimated date of arrival.
     *
     * @return DateTime
     */
    public function getEstimatedDateOfArrival(): ?DateTime;

    /**
     * Sets the estimated date of arrival.
     *
     * @param DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setEstimatedDateOfArrival(DateTime $date = null): SupplierOrderInterface;

    /**
     * Returns the tracking urls.
     *
     * @return array|null
     */
    public function getTrackingUrls(): ?array;

    /**
     * Sets the tracking urls.
     *
     * @param array $urls
     *
     * @return $this|SupplierOrderInterface
     */
    public function setTrackingUrls(array $urls = null): SupplierOrderInterface;

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription(): ?string;

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|SupplierOrderInterface
     */
    public function setDescription(string $description): SupplierOrderInterface;

    /**
     * Returns the "ordered at" date.
     *
     * @return DateTime
     */
    public function getOrderedAt(): ?DateTime;

    /**
     * Sets the "ordered at" date.
     *
     * @param DateTime $orderedAt
     *
     * @return $this|SupplierOrderInterface
     */
    public function setOrderedAt(DateTime $orderedAt = null): SupplierOrderInterface;

    /**
     * Returns the "completed at" date.
     *
     * @return DateTime
     */
    public function getCompletedAt(): ?DateTime;

    /**
     * Sets the "completed at" date.
     *
     * @param DateTime $completedAt
     *
     * @return SupplierOrderInterface
     */
    public function setCompletedAt(DateTime $completedAt = null): SupplierOrderInterface;
}

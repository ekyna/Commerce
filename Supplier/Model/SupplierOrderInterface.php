<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Class SupplierOrderInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderInterface extends
    ResourceModel\ResourceInterface,
    Common\NumberSubjectInterface,
    Common\CurrencySubjectInterface,
    Common\StateSubjectInterface,
    ResourceModel\TimestampableInterface
{
    /**
     * Returns the supplier.
     *
     * @return SupplierInterface
     */
    public function getSupplier();

    /**
     * Sets the supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return $this|SupplierOrderInterface
     */
    public function setSupplier(SupplierInterface $supplier);

    /**
     * Returns the carrier.
     *
     * @return SupplierCarrierInterface
     */
    public function getCarrier();

    /**
     * Sets the carrier.
     *
     * @param SupplierCarrierInterface $carrier
     *
     * @return $this|SupplierOrderInterface
     */
    public function setCarrier(SupplierCarrierInterface $carrier);

    /**
     * Returns whether or not the supplier order has at least one item.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns whether the supplier order has the item or not.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return bool
     */
    public function hasItem(SupplierOrderItemInterface $item);

    /**
     * Adds the item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return $this|SupplierOrderInterface
     */
    public function addItem(SupplierOrderItemInterface $item);

    /**
     * Removes the item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return $this|SupplierOrderInterface
     */
    public function removeItem(SupplierOrderItemInterface $item);

    /**
     * Returns the items.
     *
     * @return ArrayCollection|SupplierOrderItemInterface[]
     */
    public function getItems();

    /**
     * Returns whether or not the supplier order has at least one delivery.
     *
     * @return bool
     */
    public function hasDeliveries();

    /**
     * Returns whether the supplier order has the delivery or not.
     *
     * @param SupplierDeliveryInterface $delivery
     *
     * @return bool
     */
    public function hasDelivery(SupplierDeliveryInterface $delivery);

    /**
     * Adds the delivery.
     *
     * @param SupplierDeliveryInterface $delivery
     *
     * @return $this|SupplierOrderInterface
     */
    public function addDelivery(SupplierDeliveryInterface $delivery);

    /**
     * Removes the delivery.
     *
     * @param SupplierDeliveryInterface $delivery
     *
     * @return $this|SupplierOrderInterface
     */
    public function removeDelivery(SupplierDeliveryInterface $delivery);

    /**
     * Returns the deliveries.
     *
     * @return ArrayCollection|SupplierDeliveryInterface[]
     */
    public function getDeliveries();

    /**
     * Returns the shipping cost.
     *
     * @return float
     */
    public function getShippingCost();

    /**
     * Sets the shipping cost.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setShippingCost($amount);

    /**
     * Returns the customs duty.
     *
     * @return float
     */
    public function getCustomsDuty();

    /**
     * Sets the customs duty.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setCustomsDuty($amount);

    /**
     * Returns the payment total.
     *
     * @return float
     */
    public function getPaymentTotal();

    /**
     * Sets the payment total.
     *
     * @param float $amount
     *
     * @return $this|SupplierOrderInterface
     */
    public function setPaymentTotal($amount);

    /**
     * Returns the payment date.
     *
     * @return \DateTime
     */
    public function getPaymentDate();

    /**
     * Sets the payment date.
     *
     * @param \DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setPaymentDate(\DateTime $date = null);

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime
     */
    public function getEstimatedDateOfArrival();

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setEstimatedDateOfArrival(\DateTime $date = null);

    /**
     * Returns the "ordered at" date.
     *
     * @return \DateTime
     */
    public function getOrderedAt();

    /**
     * Sets the "ordered at" date.
     *
     * @param \DateTime $orderedAt
     *
     * @return $this|SupplierOrderInterface
     */
    public function setOrderedAt(\DateTime $orderedAt = null);

    /**
     * Returns the "completed at" date.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the "completed at" date.
     *
     * @param \DateTime $completedAt
     *
     * @return SupplierOrderInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);

    /**
     * Returns whether the order has attachments or not.
     *
     * @return bool
     */
    public function hasAttachments();

    /**
     * Returns whether the order has the attachment or not.
     *
     * @param SupplierOrderAttachmentInterface $attachment
     *
     * @return bool
     */
    public function hasAttachment(SupplierOrderAttachmentInterface $attachment);

    /**
     * Adds the attachment.
     *
     * @param SupplierOrderAttachmentInterface $attachment
     *
     * @return $this|SupplierOrderInterface
     */
    public function addAttachment(SupplierOrderAttachmentInterface $attachment);

    /**
     * Removes the attachment.
     *
     * @param SupplierOrderAttachmentInterface $attachment
     *
     * @return $this|SupplierOrderInterface
     */
    public function removeAttachment(SupplierOrderAttachmentInterface $attachment);

    /**
     * Returns the supplier attachments.
     *
     * @return Collection|SupplierOrderAttachmentInterface[]
     */
    public function getSupplierAttachments();

    /**
     * Returns the attachments.
     *
     * @return Collection|SupplierOrderAttachmentInterface[]
     */
    public function getAttachments();
}

<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @param float $shippingCost
     *
     * @return $this|SupplierOrderInterface
     */
    public function setShippingCost($shippingCost);

    /**
     * Returns the paymentTotal.
     *
     * @return float
     */
    public function getPaymentTotal();

    /**
     * Sets the paymentTotal.
     *
     * @param float $paymentTotal
     *
     * @return $this|SupplierOrderInterface
     */
    public function setPaymentTotal($paymentTotal);

    /**
     * Returns the paymentDate.
     *
     * @return \DateTime
     */
    public function getPaymentDate();

    /**
     * Sets the paymentDate.
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
     * Returns the orderedAt.
     *
     * @return \DateTime
     */
    public function getOrderedAt();

    /**
     * Sets the orderedAt.
     *
     * @param \DateTime $orderedAt
     *
     * @return $this|SupplierOrderInterface
     */
    public function setOrderedAt(\DateTime $orderedAt = null);

    /**
     * Returns the completed at.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the completed at.
     *
     * @param \DateTime $completedAt
     *
     * @return SupplierOrderInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}

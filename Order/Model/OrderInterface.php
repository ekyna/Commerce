<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends Common\SaleInterface, ShipmentSubjectInterface, InvoiceSubjectInterface
{
    /**
     * Sets whether the order contains sample items.
     *
     * @param bool $sample
     *
     * @return $this|OrderInterface
     */
    public function setSample($sample);

    /**
     * Sets whether the sample order is released.
     *
     * @param bool $released
     *
     * @return $this|OrderInterface
     */
    public function setReleased($released);

    /**
     * Returns whether the order is the customer's first.
     *
     * @return bool
     */
    public function isFirst();

    /**
     * Sets whether the order is the customer's first.
     *
     * @param bool $first
     *
     * @return $this|OrderInterface
     */
    public function setFirst($first);

    /**
     * Returns the origin customer.
     *
     * @return CustomerInterface
     */
    public function getOriginCustomer();

    /**
     * Sets the origin customer.
     *
     * @param CustomerInterface $customer
     *
     * @return $this|OrderInterface
     */
    public function setOriginCustomer(CustomerInterface $customer = null);

    /**
     * Returns the "completed at" datetime.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the "completed at" datetime.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|OrderInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);

    /**
     * Returns the revenue total.
     *
     * @return float
     */
    public function getRevenueTotal(): ?float;

    /**
     * Sets the revenue total.
     *
     * @param float $amount
     *
     * @return $this|OrderInterface
     */
    public function setRevenueTotal(float $amount = null): OrderInterface;

    /**
     * Returns the margin total.
     *
     * @return float
     */
    public function getMarginTotal(): ?float;

    /**
     * Sets the margin total.
     *
     * @param float $amount
     *
     * @return $this|OrderInterface
     */
    public function setMarginTotal(float $amount = null): OrderInterface;

    /**
     * Returns the items count.
     *
     * @return int
     */
    public function getItemsCount(): int;

    /**
     * Sets the items count.
     *
     * @param int $count
     *
     * @return $this|OrderInterface
     */
    public function setItemsCount(int $count): OrderInterface;
}

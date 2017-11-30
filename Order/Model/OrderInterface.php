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
}

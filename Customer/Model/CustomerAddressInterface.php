<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface CustomerAddressInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerAddressInterface extends ResourceInterface, AddressInterface
{
    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer();

    /**
     * Sets the customer.
     *
     * @param CustomerInterface $customer
     *
     * @return $this|CustomerAddressInterface
     */
    public function setCustomer(CustomerInterface $customer);

    /**
     * Returns whether this is the default invoice address or not.
     *
     * @return boolean
     */
    public function getInvoiceDefault();

    /**
     * Sets the invoice default.
     *
     * @param boolean $invoiceDefault
     *
     * @return $this|CustomerAddressInterface
     */
    public function setInvoiceDefault($invoiceDefault);

    /**
     * Returns whether this is the default delivery address or not
     *
     * @return boolean
     */
    public function getDeliveryDefault();

    /**
     * Sets the deliveryDefault.
     *
     * @param boolean $deliveryDefault
     *
     * @return $this|CustomerAddressInterface
     */
    public function setDeliveryDefault($deliveryDefault);
}

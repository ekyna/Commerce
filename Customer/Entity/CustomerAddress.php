<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class CustomerAddress
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddress extends AbstractAddress implements CustomerAddressInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var boolean
     */
    protected $invoiceDefault;

    /**
     * @var boolean
     */
    protected $deliveryDefault;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->invoiceDefault = false;
        $this->deliveryDefault = false;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s %s %s', $this->street, $this->postalCode, $this->city);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @inheritdoc
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isInvoiceDefault()
    {
        return $this->invoiceDefault;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceDefault($invoiceDefault)
    {
        $this->invoiceDefault = (bool)$invoiceDefault;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isDeliveryDefault()
    {
        return $this->deliveryDefault;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryDefault($deliveryDefault)
    {
        $this->deliveryDefault = (bool)$deliveryDefault;

        return $this;
    }
}

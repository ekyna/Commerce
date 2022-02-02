<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Model\ResourceTrait;

/**
 * Class CustomerAddress
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddress extends AbstractAddress implements CustomerAddressInterface
{
    use ResourceTrait;

    protected ?CustomerInterface $customer        = null;
    protected bool               $invoiceDefault  = false;
    protected bool               $deliveryDefault = false;

    public function __clone()
    {
        $this->id = null;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): CustomerAddressInterface
    {
        if ($this->customer === $customer) {
            return $this;
        }

        if ($previous = $this->customer) {
            $this->customer = null;
            $previous->removeAddress($this);
        }

        if ($this->customer = $customer) {
            $this->customer->addAddress($this);
        }

        return $this;
    }

    public function isInvoiceDefault(): bool
    {
        return $this->invoiceDefault;
    }

    public function setInvoiceDefault(bool $default): CustomerAddressInterface
    {
        $this->invoiceDefault = $default;

        return $this;
    }

    public function isDeliveryDefault(): bool
    {
        return $this->deliveryDefault;
    }

    public function setDeliveryDefault(bool $default): CustomerAddressInterface
    {
        $this->deliveryDefault = $default;

        return $this;
    }
}

<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\IdentityTrait;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierAddressInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierCarrierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Resource\Model\LocalizedTrait;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class Supplier
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Supplier implements SupplierInterface
{
    use IdentityTrait,
        TimestampableTrait,
        LocalizedTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $customerCode;

    /**
     * @var SupplierAddress
     */
    protected $address;

    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @var TaxInterface
     */
    protected $tax;

    /**
     * @var SupplierCarrierInterface
     */
    protected $carrier;

    /**
     * @var string
     */
    protected $description;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New supplier';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerCode()
    {
        return $this->customerCode;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerCode($code)
    {
        $this->customerCode = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @inheritdoc
     */
    public function setAddress(SupplierAddressInterface $address = null)
    {
        if ((null !== $this->address) && ($this->address !== $address)) {
            $this->address->setSupplier(null);
        }

        if ($address) {
            $address->setSupplier($this);
        }

        $this->address = $address;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(CurrencyInterface $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @inheritdoc
     */
    public function setTax(TaxInterface $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * @inheritdoc
     */
    public function setCarrier(SupplierCarrierInterface $carrier = null)
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}

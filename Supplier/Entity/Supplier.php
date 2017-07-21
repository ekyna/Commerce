<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\IdentityTrait;
use Ekyna\Component\Commerce\Supplier\Model\SupplierAddressInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;

/**
 * Class Supplier
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Supplier implements SupplierInterface
{
    use IdentityTrait;

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
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getName();
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
        $address->setSupplier($this);

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
}

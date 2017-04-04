<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model as Model;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermSubjectTrait;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class Customer
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer implements Model\CustomerInterface
{
    use Common\IdentityTrait,
        Common\NumberSubjectTrait,
        PaymentTermSubjectTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $mobile;

    /**
     * @var Model\CustomerInterface
     */
    protected $parent;

    /**
     * @var ArrayCollection|Model\CustomerInterface[]
     */
    protected $children;

    /**
     * @var Model\CustomerGroupInterface
     */
    protected $customerGroup;

    /**
     * @var ArrayCollection|Model\CustomerAddressInterface[]
     */
    protected $addresses;

    /**
     * @var float
     */
    protected $outstandingLimit;

    /**
     * @var float
     */
    protected $outstandingBalance;

    /**
     * @var string
     */
    protected $description;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->outstandingLimit = 0;
        $this->outstandingBalance = 0;

        $this->children = new ArrayCollection();
        $this->addresses = new ArrayCollection();

        $this->createdAt = new \DateTime();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        $sign = '';
        if ($this->hasParent()) {
            $sign = '&loz;';
        } elseif ($this->hasChildren()) {
            $sign = '&diams;';
        }

        if (0 < strlen($this->company)) {
            return sprintf('%s [%s] %s %s', $sign, $this->company, $this->firstName, $this->lastName);
        }

        return sprintf('%s %s %s', $sign, $this->firstName, $this->lastName);
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @inheritdoc
     */
    public function setCompany($company)
    {
        $this->company = $company;

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
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @inheritdoc
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @inheritdoc
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasParent()
    {
        return null !== $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setParent(Model\CustomerInterface $parent = null)
    {
        if ($parent !== $this->parent) {
            if (null !== $this->parent) {
                $this->parent->removeChild($this);
            }
            if (null !== $parent) {
                $parent->addChild($this);
            }
            $this->parent = $parent;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function hasChild(Model\CustomerInterface $child)
    {
        return $this->children->contains($child);
    }

    /**
     * @inheritdoc
     */
    public function addChild(Model\CustomerInterface $child)
    {
        if (!$this->hasChild($child)) {
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChild(Model\CustomerInterface $child)
    {
        if ($this->hasChild($child)) {
            $this->children->removeElement($child);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasChildren()
    {
        return 0 < $this->children->count();
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroup(Model\CustomerGroupInterface $customerGroup)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @inheritdoc
     */
    public function hasAddress(Model\CustomerAddressInterface $address)
    {
        return $this->addresses->contains($address);
    }

    /**
     * @inheritdoc
     */
    public function addAddress(Model\CustomerAddressInterface $address)
    {
        if (!$this->hasAddress($address)) {
            $address->setCustomer($this);
            $this->addresses->add($address);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAddress(Model\CustomerAddressInterface $address)
    {
        if ($this->hasAddress($address)) {
            $address->setCustomer(null);
            $this->addresses->removeElement($address);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultInvoiceAddress()
    {
        if (0 < $this->addresses->count()) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq('invoiceDefault', true))
                ->setMaxResults(1);

            $matches = $this->addresses->matching($criteria);
            if ($matches->count() == 1) {
                return $matches->first();
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultDeliveryAddress()
    {
        if (0 < $this->addresses->count()) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq('deliveryDefault', true))
                ->setMaxResults(1);

            $matches = $this->addresses->matching($criteria);
            if ($matches->count() == 1) {
                return $matches->first();
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getOutstandingLimit()
    {
        return $this->outstandingLimit;
    }

    /**
     * @inheritdoc
     */
    public function setOutstandingLimit($limit)
    {
        $this->outstandingLimit = (float)$limit;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOutstandingBalance()
    {
        return $this->outstandingBalance;
    }

    /**
     * @inheritdoc
     */
    public function setOutstandingBalance($amount)
    {
        $this->outstandingBalance = $amount;

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

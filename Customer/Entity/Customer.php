<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model as Model;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermSubjectTrait;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectTrait;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class Customer
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer implements Model\CustomerInterface
{
    use Common\IdentityTrait,
        Common\NumberSubjectTrait,
        Common\CurrencySubjectTrait,
        PaymentTermSubjectTrait,
        VatNumberSubjectTrait,
        RM\LocalizedTrait,
        RM\TimestampableTrait;

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
     * @var \DateTime
     */
    protected $birthday;

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
    protected $creditBalance;

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
    protected $state;

    /**
     * @var string
     */
    protected $description;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->creditBalance = 0;
        $this->outstandingLimit = 0;
        $this->outstandingBalance = 0;

        $this->state = Model\CustomerStates::STATE_NEW;

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
        if ($this->company) {
            $sign = '';
            if ($this->hasParent()) {
                $sign = '♦'; //'&loz;';
            } elseif ($this->hasChildren()) { // TODO Greedy : triggers collection initialization
                $sign = '◊'; //'&diams;';
            }
            return sprintf('%s [%s] %s %s', $sign, $this->company, $this->lastName, $this->firstName);
        }

        return sprintf('%s %s', $this->lastName, $this->firstName);
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
     * Returns the birthday.
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Sets the birthday.
     *
     * @param \DateTime $birthday
     *
     * @return Customer
     */
    public function setBirthday(\DateTime $birthday = null)
    {
        $this->birthday = $birthday;

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
            if ($previous = $this->parent) {
                $previous = null;
                $this->parent->removeChild($this);
            }

            if ($this->parent = $parent) {
                $parent->addChild($this);
            }
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
            $child->setParent($this);
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
            $child->setParent(null);
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
    public function setCustomerGroup(Model\CustomerGroupInterface $group = null)
    {
        $this->customerGroup = $group;

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
            $this->addresses->add($address);
            $address->setCustomer($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAddress(Model\CustomerAddressInterface $address)
    {
        if ($this->hasAddress($address)) {
            $this->addresses->removeElement($address);
            $address->setCustomer(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreditBalance()
    {
        return $this->creditBalance;
    }

    /**
     * @inheritdoc
     */
    public function setCreditBalance($creditBalance)
    {
        $this->creditBalance = (float)$creditBalance;

        return $this;
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
        $this->outstandingBalance = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState($state)
    {
        $this->state = $state;

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

    /**
     * @inheritdoc
     */
    public function getDefaultInvoiceAddress($allowParentAddress = false)
    {
        if ($allowParentAddress && $this->hasParent()) {
            if (null !== $address = $this->parent->getDefaultInvoiceAddress($allowParentAddress)) {
                return $address;
            }
        }

        if (null !== $address = $this->findOneAddressBy(Criteria::expr()->eq('invoiceDefault', true))) {
            return $address;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultDeliveryAddress($allowParentAddress = false)
    {
        if ($allowParentAddress && $this->hasParent()) {
            if (null !== $address = $this->parent->getDefaultDeliveryAddress($allowParentAddress)) {
                return $address;
            }
        }

        if (null !== $address = $this->findOneAddressBy(Criteria::expr()->eq('deliveryDefault', true))) {
            return $address;
        }

        return null;
    }

    /**
     * Finds one address by expression.
     *
     * @param mixed $expression
     *
     * @return Model\CustomerAddressInterface|null
     */
    protected function findOneAddressBy($expression)
    {
        if (0 < $this->addresses->count()) {
            $criteria = Criteria::create()
                ->where($expression)
                ->setMaxResults(1);

            $matches = $this->addresses->matching($criteria);
            if ($matches->count() == 1) {
                return $matches->first();
            }
        }

        return null;
    }
}

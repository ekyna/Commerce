<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\PriceListInterface;

/**
 * Class Customer
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer implements CustomerInterface
{
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
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

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
     * @var CustomerInterface
     */
    protected $parent;

    /**
     * @var ArrayCollection|CustomerInterface[]
     */
    protected $children;

    /**
     * @var ArrayCollection|CustomerGroupInterface[]
     */
    protected $customerGroups;

    /**
     * @var ArrayCollection|CustomerAddressInterface[]
     */
    protected $addresses;

    /**
     * @var ArrayCollection|PriceListInterface[]
     */
    protected $priceLists;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->priceLists = new ArrayCollection();
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
            $sign = '&loz; ';
        } elseif ($this->hasChildren()) {
            $sign = '&diams; ';
        }

        return sprintf('%s%s %s', $sign, $this->firstName, $this->lastName);
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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @inheritdoc
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @inheritdoc
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

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
     *  @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     *  @inheritdoc
     */
    public function setParent(CustomerInterface $parent = null)
    {
        if ($parent !== $this->parent) {
            if (null !== $this->parent) {
                /** @noinspection PhpInternalEntityUsedInspection */
                $this->parent->removeChild($this);
            }
            if (null !== $parent) {
                /** @noinspection PhpInternalEntityUsedInspection */
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
    public function hasChild(CustomerInterface $child)
    {
        return $this->children->contains($child);
    }

    /**
     * @inheritdoc
     */
    public function addChild(CustomerInterface $child)
    {
        if (!$this->hasChild($child)) {
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChild(CustomerInterface $child)
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
    public function setChildren(ArrayCollection $children)
    {
        $this->children = new ArrayCollection();
        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomerGroup(CustomerGroupInterface $group)
    {
        return $this->customerGroups->contains($group);
    }

    /**
     * @inheritdoc
     */
    public function addCustomerGroup(CustomerGroupInterface $group)
    {
        if (!$this->hasCustomerGroup($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCustomerGroup(CustomerGroupInterface $group)
    {
        if ($this->hasCustomerGroup($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroups(ArrayCollection $customerGroups)
    {
        $this->customerGroups = new ArrayCollection();
        foreach ($customerGroups as $group) {
            $this->addCustomerGroup($group);
        }

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
    public function hasAddress(CustomerAddressInterface $address)
    {
        return $this->addresses->contains($address);
    }

    /**
     * @inheritdoc
     */
    public function addAddress(CustomerAddressInterface $address)
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
    public function removeAddress(CustomerAddressInterface $address)
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
    public function setAddresses(ArrayCollection $addresses)
    {
        $this->addresses = new ArrayCollection();
        foreach ($addresses as $address) {
            $this->addAddress($address);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriceLists()
    {
        return $this->priceLists;
    }

    /**
     * @inheritdoc
     */
    public function hasPriceList(PriceListInterface $priceList)
    {
        return $this->priceLists->contains($priceList);
    }

    /**
     * @inheritdoc
     */
    public function addPriceList(PriceListInterface $priceList)
    {
        if (!$this->hasPriceList($priceList)) {
            $this->priceLists->add($priceList);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePriceList(PriceListInterface $priceList)
    {
        if ($this->hasPriceList($priceList)) {
            $this->priceLists->removeElement($priceList);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPriceLists(ArrayCollection $priceLists)
    {
        $this->priceLists = new ArrayCollection();
        foreach ($priceLists as $priceList) {
            $this->addPriceList($priceList);
        }
        $this->priceLists = $priceLists;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

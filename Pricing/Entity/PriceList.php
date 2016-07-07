<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\PriceListInterface;

/**
 * Class PriceList
 * @package Ekyna\Component\Commerce\Pricing\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceList implements PriceListInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection|CustomerGroupInterface[]
     */
    protected $customerGroups;

    /**
     * @var ArrayCollection|CustomerInterface[]
     */
    protected $customers;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->customerGroups = new ArrayCollection();
        $this->customers = new ArrayCollection();
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
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomerGroup(CustomerGroupInterface $customerGroup)
    {
        return $this->customerGroups->contains($customerGroup);
    }

    /**
     * @inheritdoc
     */
    public function addCustomerGroup(CustomerGroupInterface $customerGroup)
    {
        if (!$this->hasCustomerGroup($customerGroup)) {
            $this->customerGroups->add($customerGroup);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCustomerGroup(CustomerGroupInterface $customerGroup)
    {
        if ($this->hasCustomerGroup($customerGroup)) {
            $this->customerGroups->removeElement($customerGroup);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroups(ArrayCollection $customerGroups)
    {
        $this->customerGroups = $customerGroups;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomer(CustomerInterface $customer)
    {
        return $this->customers->contains($customer);
    }

    /**
     * @inheritdoc
     */
    public function addCustomer(CustomerInterface $customer)
    {
        if (!$this->hasCustomer($customer)) {
            $this->customers->add($customer);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCustomer(CustomerInterface $customer)
    {
        if ($this->hasCustomer($customer)) {
            $this->customers->removeElement($customer);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCustomers(ArrayCollection $customers)
    {
        $this->customers = $customers;
        return $this;
    }
}

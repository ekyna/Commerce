<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\EntityInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Interface PriceListInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PriceListInterface extends EntityInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     * @return $this|PriceListInterface
     */
    public function setName($name);

    /**
     * Returns the customer groups.
     *
     * @return ArrayCollection|CustomerGroupInterface[]
     */
    public function getCustomerGroups();

    /**
     * Returns whether the price list has the customer group or not.
     *
     * @param CustomerGroupInterface $customerGroup
     *
     * @return bool
     */
    public function hasCustomerGroup(CustomerGroupInterface $customerGroup);

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $customerGroup
     *
     * @return $this|PriceListInterface
     */
    public function addCustomerGroup(CustomerGroupInterface $customerGroup);

    /**
     * Removes the customer group.
     *
     * @param CustomerGroupInterface $customerGroup
     *
     * @return $this|PriceListInterface
     */
    public function removeCustomerGroup(CustomerGroupInterface $customerGroup);

    /**
     * Sets the customer groups.
     *
     * @param ArrayCollection $customerGroups
     *
     * @return $this|PriceListInterface
     */
    public function setCustomerGroups(ArrayCollection $customerGroups);

    /**
     * Returns the customers.
     *
     * @return ArrayCollection|CustomerInterface[]
     */
    public function getCustomers();

    /**
     * Returns whether the price list has the customer or not.
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function hasCustomer(CustomerInterface $customer);

    /**
     * Adds the customer.
     *
     * @param CustomerInterface $customer
     * @return $this|PriceListInterface
     */
    public function addCustomer(CustomerInterface $customer);

    /**
     * Removes the customer.
     *
     * @param CustomerInterface $customer
     * @return $this|PriceListInterface
     */
    public function removeCustomer(CustomerInterface $customer);

    /**
     * Sets the customers.
     *
     * @param ArrayCollection $customers
     * @return $this|PriceListInterface
     */
    public function setCustomers(ArrayCollection $customers);
}

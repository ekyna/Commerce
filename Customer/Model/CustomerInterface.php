<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\EntityInterface;
use Ekyna\Component\Commerce\Pricing\Model\PriceListInterface;

/**
 * Interface CustomerInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerInterface extends EntityInterface
{
    /**
     * Returns the company.
     *
     * @return string
     */
    public function getCompany();

    /**
     * Sets the company.
     *
     * @param string $company
     *
     * @return $this|CustomerInterface
     */
    public function setCompany($company);

    /**
     * Returns the firstName.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Sets the firstName.
     *
     * @param string $firstName
     * @return $this|CustomerInterface
     */
    public function setFirstName($firstName);

    /**
     * Returns the lastName.
     *
     * @return string
     */
    public function getLastName();

    /**
     * Sets the lastName.
     *
     * @param string $lastName
     * @return $this|CustomerInterface
     */
    public function setLastName($lastName);

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Sets the email.
     *
     * @param string $email
     * @return $this|CustomerInterface
     */
    public function setEmail($email);

    /**
     * Returns the phone.
     *
     * @return string
     */
    public function getPhone();

    /**
     * Sets the phone.
     *
     * @param string $phone
     * @return $this|CustomerInterface
     */
    public function setPhone($phone);

    /**
     * Returns the mobile.
     *
     * @return string
     */
    public function getMobile();

    /**
     * Sets the mobile.
     *
     * @param string $mobile
     * @return $this|CustomerInterface
     */
    public function setMobile($mobile);

    /**
     * Returns whether the customer has a parent or not.
     *
     * @return bool
     */
    public function hasParent();

    /**
     * Returns the parent.
     *
     * @return CustomerInterface
     */
    public function getParent();

    /**
     * Sets the parent.
     *
     * @param CustomerInterface $parent
     *
     * @return $this|CustomerInterface
     */
    public function setParent(CustomerInterface $parent = null);

    /**
     * Returns the children.
     *
     * @return ArrayCollection|CustomerInterface[]
     */
    public function getChildren();

    /**
     * Returns whether the customer has the child or not.
     *
     * @param CustomerInterface $child
     *
     * @return bool
     */
    public function hasChild(CustomerInterface $child);

    /**
     * Adds the child.
     *
     * @param CustomerInterface $child
     *
     * @return $this|CustomerInterface
     * @internal
     */
    public function addChild(CustomerInterface $child);

    /**
     * Removes the child.
     *
     * @param CustomerInterface $child
     *
     * @return $this|CustomerInterface
     * @internal
     */
    public function removeChild(CustomerInterface $child);

    /**
     * Returns whether the customer has children or not.
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Sets the children.
     *
     * @param ArrayCollection $children
     *
     * @return $this|CustomerInterface
     * @internal
     */
    public function setChildren(ArrayCollection $children);

    /**
     * Returns the groups.
     *
     * @return ArrayCollection|CustomerGroupInterface[]
     */
    public function getCustomerGroups();

    /**
     * Returns whether the customer has the group or not.
     *
     * @param CustomerGroupInterface $group
     * @return bool
     */
    public function hasCustomerGroup(CustomerGroupInterface $group);

    /**
     * Adds the group.
     *
     * @param CustomerGroupInterface $group
     * @return $this|CustomerInterface
     */
    public function addCustomerGroup(CustomerGroupInterface $group);

    /**
     * Removes the group.
     *
     * @param CustomerGroupInterface $group
     * @return $this|CustomerInterface
     */
    public function removeCustomerGroup(CustomerGroupInterface $group);

    /**
     * Sets the groups.
     *
     * @param ArrayCollection $groups
     * @return $this|CustomerInterface
     */
    public function setCustomerGroups(ArrayCollection $groups);

    /**
     * Returns the addresses.
     *
     * @return ArrayCollection|CustomerAddressInterface[]
     */
    public function getAddresses();

    /**
     * Returns whether the customer has the address or not.
     *
     * @param CustomerAddressInterface $address
     * @return bool
     */
    public function hasAddress(CustomerAddressInterface $address);

    /**
     * Adds the address.
     *
     * @param CustomerAddressInterface $address
     * @return $this|CustomerInterface
     */
    public function addAddress(CustomerAddressInterface $address);

    /**
     * Removes the address.
     *
     * @param CustomerAddressInterface $address
     * @return $this|CustomerInterface
     */
    public function removeAddress(CustomerAddressInterface $address);

    /**
     * Sets the addresses.
     *
     * @param ArrayCollection $addresses
     * @return $this|CustomerInterface
     */
    public function setAddresses(ArrayCollection $addresses);

    /**
     * Returns the price lists.
     *
     * @return ArrayCollection|PriceListInterface[]
     */
    public function getPriceLists();

    /**
     * Returns whether the customer has the price list or not.
     *
     * @param PriceListInterface $priceList
     * @return bool
     */
    public function hasPriceList(PriceListInterface $priceList);

    /**
     * Adds the price list.
     *
     * @param PriceListInterface $priceList
     * @return $this|CustomerInterface
     */
    public function addPriceList(PriceListInterface $priceList);

    /**
     * Removes the price list.
     *
     * @param PriceListInterface $priceList
     * @return $this|CustomerInterface
     */
    public function removePriceList(PriceListInterface $priceList);

    /**
     * Sets the price lists.
     *
     * @param ArrayCollection|PriceListInterface[] $priceLists
     * @return $this|CustomerInterface
     */
    public function setPriceLists(ArrayCollection $priceLists);

    /**
     * Returns the "created at" datetime.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets the "created at" datetime.
     *
     * @param \DateTime $createdAt
     *
     * @return $this|CustomerInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * Returns the "updated at" datetime.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Sets the "updated at" datetime.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this|CustomerInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);
}

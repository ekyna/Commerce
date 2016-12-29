<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Commerce\Pricing\Model\PriceListInterface;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface CustomerInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerInterface extends
    ResourceModel\ResourceInterface,
    ResourceModel\TimestampableInterface,
    IdentityInterface
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
     * Returns the email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return $this|CustomerInterface
     */
    public function setEmail($email);

    /**
     * Returns the phone.
     *
     * @return \libphonenumber\PhoneNumber|string
     */
    public function getPhone();

    /**
     * Sets the phone.
     *
     * @param string $phone
     *
     * @return $this|CustomerInterface
     */
    public function setPhone($phone);

    /**
     * Returns the mobile.
     *
     * @return \libphonenumber\PhoneNumber|string
     */
    public function getMobile();

    /**
     * Sets the mobile.
     *
     * @param string $mobile
     *
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
     * Returns the customer group.
     *
     * @return CustomerGroupInterface
     */
    public function getCustomerGroup();

    /**
     * Sets the customer group.
     *
     * @param CustomerGroupInterface $customerGroup
     *
     * @return $this|CustomerInterface
     */
    public function setCustomerGroup(CustomerGroupInterface $customerGroup);

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
     *
     * @return bool
     */
    public function hasAddress(CustomerAddressInterface $address);

    /**
     * Adds the address.
     *
     * @param CustomerAddressInterface $address
     *
     * @return $this|CustomerInterface
     */
    public function addAddress(CustomerAddressInterface $address);

    /**
     * Removes the address.
     *
     * @param CustomerAddressInterface $address
     *
     * @return $this|CustomerInterface
     */
    public function removeAddress(CustomerAddressInterface $address);

    /**
     * Returns the default invoice address.
     *
     * @return CustomerAddressInterface|null
     */
    public function getDefaultInvoiceAddress();

    /**
     * Returns the default delivery address.
     *
     * @return CustomerAddressInterface|null
     */
    public function getDefaultDeliveryAddress();

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
     *
     * @return bool
     */
    public function hasPriceList(PriceListInterface $priceList);

    /**
     * Adds the price list.
     *
     * @param PriceListInterface $priceList
     *
     * @return $this|CustomerInterface
     */
    public function addPriceList(PriceListInterface $priceList);

    /**
     * Removes the price list.
     *
     * @param PriceListInterface $priceList
     *
     * @return $this|CustomerInterface
     */
    public function removePriceList(PriceListInterface $priceList);
}

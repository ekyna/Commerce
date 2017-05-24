<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermSubjectInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectInterface;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface CustomerInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerInterface extends
    ResourceModel\ResourceInterface,
    ResourceModel\TimestampableInterface,
    Common\IdentityInterface,
    Common\NumberSubjectInterface,
    PaymentTermSubjectInterface,
    VatNumberSubjectInterface
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
     */
    public function addChild(CustomerInterface $child);

    /**
     * Removes the child.
     *
     * @param CustomerInterface $child
     *
     * @return $this|CustomerInterface
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
     * Returns the credit balance.
     *
     * @return float
     */
    public function getCreditBalance();

    /**
     * Sets the credit balance.
     *
     * @param float $creditBalance
     *
     * @return $this|CustomerInterface
     */
    public function setCreditBalance($creditBalance);

    /**
     * Returns the outstanding limit.
     *
     * @return float
     */
    public function getOutstandingLimit();

    /**
     * Sets the outstanding limit.
     *
     * @param float $limit
     *
     * @return $this|CustomerInterface
     */
    public function setOutstandingLimit($limit);

    /**
     * Returns the outstanding balance.
     *
     * @return float
     */
    public function getOutstandingBalance();

    /**
     * Sets the outstanding balance.
     *
     * @param float $amount
     *
     * @return $this|CustomerInterface
     */
    public function setOutstandingBalance($amount);

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|CustomerInterface
     */
    public function setDescription($description);
}

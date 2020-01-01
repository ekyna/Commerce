<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface CustomerInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerInterface extends
    RM\ResourceInterface,
    RM\LocalizedInterface,
    RM\TimestampableInterface,
    Common\IdentityInterface,
    Common\NumberSubjectInterface,
    Common\CurrencySubjectInterface,
    Payment\PaymentTermSubjectInterface,
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
     * Returns the birthday.
     *
     * @return \DateTime
     */
    public function getBirthday();

    /**
     * Sets the birthday.
     *
     * @param \DateTime $birthday
     *
     * @return $this|CustomerInterface
     */
    public function setBirthday(\DateTime $birthday = null);

    /**
     * Returns whether this customer subscribes to the newsletter.
     *
     * @return bool
     */
    public function isNewsletter(): bool;

    /**
     * Sets whether this customer subscribes to the newsletter.
     *
     * @param bool $newsletter
     *
     * @return CustomerInterface
     */
    public function setNewsletter(bool $newsletter): CustomerInterface;

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
     * @param CustomerGroupInterface $group
     *
     * @return $this|CustomerInterface
     */
    public function setCustomerGroup(CustomerGroupInterface $group = null);

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
     * Returns the default payment method.
     *
     * @return Payment\PaymentMethodInterface
     */
    public function getDefaultPaymentMethod(): ?Payment\PaymentMethodInterface;

    /**
     * Sets the default payment method.
     *
     * @param Payment\PaymentMethodInterface $method
     *
     * @return $this|CustomerInterface
     */
    public function setDefaultPaymentMethod(Payment\PaymentMethodInterface $method = null): CustomerInterface;

    /**
     * Returns the payment methods.
     *
     * @return ArrayCollection|Payment\PaymentMethodInterface[]
     */
    public function getPaymentMethods(): Collection;

    /**
     * Returns whether the customer has the payment method or not.
     *
     * @param Payment\PaymentMethodInterface $paymentMethod
     *
     * @return bool
     */
    public function hasPaymentMethod(Payment\PaymentMethodInterface $paymentMethod): bool;

    /**
     * Adds the payment method.
     *
     * @param Payment\PaymentMethodInterface $paymentMethod
     *
     * @return $this|CustomerInterface
     */
    public function addPaymentMethod(Payment\PaymentMethodInterface $paymentMethod): CustomerInterface;

    /**
     * Removes the payment method.
     *
     * @param Payment\PaymentMethodInterface $paymentMethod
     *
     * @return $this|CustomerInterface
     */
    public function removePaymentMethod(Payment\PaymentMethodInterface $paymentMethod): CustomerInterface;

    /**
     * Returns the loyalty points.
     *
     * @return int
     */
    public function getLoyaltyPoints(): int;

    /**
     * Sets the loyalty points.
     *
     * @param int $points
     *
     * @return CustomerInterface
     */
    public function setLoyaltyPoints(int $points): CustomerInterface;

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
     * Returns whether outstanding overflow is allowed (by setting custom limit on sales).
     *
     * @return bool
     */
    public function isOutstandingOverflow(): bool;

    /**
     * Sets whether outstanding overflow is allowed (by setting custom limit on sales).
     *
     * @param bool $overflow
     *
     * @return $this|CustomerInterface
     */
    public function setOutstandingOverflow(bool $overflow): CustomerInterface;

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
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|CustomerInterface
     */
    public function setState($state);

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

    /**
     * Sets the locale
     *
     * @param string|null $locale
     *
     * @return $this|CustomerInterface
     */
    public function setLocale(?string $locale);

    /**
     * Returns the default invoice address.
     *
     * @param bool $allowParentAddress
     *
     * @return CustomerAddressInterface|null
     */
    public function getDefaultInvoiceAddress($allowParentAddress = false);

    /**
     * Returns the default delivery address.
     *
     * @param bool $allowParentAddress
     *
     * @return CustomerAddressInterface|null
     */
    public function getDefaultDeliveryAddress($allowParentAddress = false);
}

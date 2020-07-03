<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Entity\CustomerContact;
use Ekyna\Component\Commerce\Customer\Entity\CustomerLogo;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectInterface;
use Ekyna\Component\Resource\Model as Resource;
use libphonenumber\PhoneNumber;

/**
 * Interface CustomerInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerInterface extends
    Resource\ResourceInterface,
    Resource\LocalizedInterface,
    Resource\TimestampableInterface,
    Common\IdentityInterface,
    Common\KeySubjectInterface,
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
    public function getCompany(): ?string;

    /**
     * Sets the company.
     *
     * @param string $company
     *
     * @return $this|CustomerInterface
     */
    public function setCompany(string $company = null): CustomerInterface;

    /**
     * Returns the company number.
     *
     * @return string
     */
    public function getCompanyNumber(): ?string;

    /**
     * Sets the company number.
     *
     * @param string $number
     *
     * @return $this|CustomerInterface
     */
    public function setCompanyNumber(string $number = null): CustomerInterface;

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail(): ?string;

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return $this|CustomerInterface
     */
    public function setEmail(string $email = null): CustomerInterface;

    /**
     * Returns the phone.
     *
     * @return PhoneNumber
     */
    public function getPhone(): ?PhoneNumber;

    /**
     * Sets the phone.
     *
     * @param PhoneNumber $phone
     *
     * @return $this|CustomerInterface
     */
    public function setPhone(PhoneNumber $phone = null): CustomerInterface;

    /**
     * Returns the mobile.
     *
     * @return PhoneNumber
     */
    public function getMobile(): ?PhoneNumber;

    /**
     * Sets the mobile.
     *
     * @param PhoneNumber $mobile
     *
     * @return $this|CustomerInterface
     */
    public function setMobile(PhoneNumber $mobile = null): CustomerInterface;

    /**
     * Returns the birthday.
     *
     * @return DateTime
     */
    public function getBirthday(): ?DateTime;

    /**
     * Sets the birthday.
     *
     * @param DateTime $birthday
     *
     * @return $this|CustomerInterface
     */
    public function setBirthday(DateTime $birthday = null): CustomerInterface;

    /**
     * Returns whether the customer has a parent or not.
     *
     * @return bool
     */
    public function hasParent(): bool;

    /**
     * Returns the parent.
     *
     * @return CustomerInterface
     */
    public function getParent(): ?CustomerInterface;

    /**
     * Sets the parent.
     *
     * @param CustomerInterface $parent
     *
     * @return $this|CustomerInterface
     */
    public function setParent(CustomerInterface $parent = null): CustomerInterface;

    /**
     * Returns the children.
     *
     * @return Collection|CustomerInterface[]
     */
    public function getChildren(): Collection;

    /**
     * Returns whether the customer has the child or not.
     *
     * @param CustomerInterface $child
     *
     * @return bool
     */
    public function hasChild(CustomerInterface $child): bool;

    /**
     * Adds the child.
     *
     * @param CustomerInterface $child
     *
     * @return $this|CustomerInterface
     */
    public function addChild(CustomerInterface $child): CustomerInterface;

    /**
     * Removes the child.
     *
     * @param CustomerInterface $child
     *
     * @return $this|CustomerInterface
     */
    public function removeChild(CustomerInterface $child): CustomerInterface;

    /**
     * Returns whether the customer has children or not.
     *
     * @return bool
     */
    public function hasChildren(): bool;

    /**
     * Returns the customer group.
     *
     * @return CustomerGroupInterface
     */
    public function getCustomerGroup(): ?CustomerGroupInterface;

    /**
     * Sets the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|CustomerInterface
     */
    public function setCustomerGroup(CustomerGroupInterface $group = null): CustomerInterface;

    /**
     * Returns the addresses.
     *
     * @return Collection|CustomerAddressInterface[]
     */
    public function getAddresses(): Collection;

    /**
     * Returns whether the customer has the address or not.
     *
     * @param CustomerAddressInterface $address
     *
     * @return bool
     */
    public function hasAddress(CustomerAddressInterface $address): bool;

    /**
     * Adds the address.
     *
     * @param CustomerAddressInterface $address
     *
     * @return $this|CustomerInterface
     */
    public function addAddress(CustomerAddressInterface $address): CustomerInterface;

    /**
     * Removes the address.
     *
     * @param CustomerAddressInterface $address
     *
     * @return $this|CustomerInterface
     */
    public function removeAddress(CustomerAddressInterface $address): CustomerInterface;

    /**
     * Returns the contacts.
     *
     * @return Collection|CustomerContact[]
     */
    public function getContacts(): Collection;

    /**
     * Returns whether the customer has the contact or not.
     *
     * @param CustomerContact $contact
     *
     * @return bool
     */
    public function hasContact(CustomerContact $contact): bool;

    /**
     * Adds the contact.
     *
     * @param CustomerContact $contact
     *
     * @return $this|CustomerInterface
     */
    public function addContact(CustomerContact $contact): CustomerInterface;

    /**
     * Removes the contact.
     *
     * @param CustomerContact $contact
     *
     * @return $this|CustomerInterface
     */
    public function removeContact(CustomerContact $contact): CustomerInterface;

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
     * @return Collection|Payment\PaymentMethodInterface[]
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
     * Returns the branding logo.
     *
     * @return CustomerLogo|null
     */
    public function getBrandLogo(): ?CustomerLogo;

    /**
     * Sets the branding logo.
     *
     * @param CustomerLogo $logo
     *
     * @return $this|CustomerInterface
     */
    public function setBrandLogo(CustomerLogo $logo = null): CustomerInterface;

    /**
     * Returns the branding color.
     *
     * @return string|null
     */
    public function getBrandColor(): ?string;

    /**
     * Sets the branding color.
     *
     * @param string $color
     *
     * @return $this|CustomerInterface
     */
    public function setBrandColor(string $color = null): CustomerInterface;

    /**
     * Returns the branding url.
     *
     * @return string|null
     */
    public function getBrandUrl(): ?string;

    /**
     * Sets the branding url.
     *
     * @param string $url
     *
     * @return $this|CustomerInterface
     */
    public function setBrandUrl(string $url = null): CustomerInterface;

    /**
     * Returns the document footer.
     *
     * @return string
     */
    public function getDocumentFooter(): ?string;

    /**
     * Sets the document footer.
     *
     * @param string $html
     *
     * @return $this|CustomerInterface
     */
    public function setDocumentFooter(string $html = null): CustomerInterface;

    /**
     * Returns the document types (logo and color usage).
     *
     * @return string[]
     */
    public function getDocumentTypes(): array;

    /**
     * Sets the document types (logo and color usage).
     *
     * @param array $types
     *
     * @return $this|CustomerInterface
     */
    public function setDocumentTypes(array $types): CustomerInterface;

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
     * @return $this|CustomerInterface
     */
    public function setLoyaltyPoints(int $points): CustomerInterface;

    /**
     * Returns the credit balance.
     *
     * @return float
     */
    public function getCreditBalance(): float;

    /**
     * Sets the credit balance.
     *
     * @param float $creditBalance
     *
     * @return $this|CustomerInterface
     */
    public function setCreditBalance(float $creditBalance): CustomerInterface;

    /**
     * Returns the outstanding limit.
     *
     * @return float
     */
    public function getOutstandingLimit(): float;

    /**
     * Sets the outstanding limit.
     *
     * @param float $limit
     *
     * @return $this|CustomerInterface
     */
    public function setOutstandingLimit(float $limit): CustomerInterface;

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
    public function getOutstandingBalance(): float;

    /**
     * Sets the outstanding balance.
     *
     * @param float $amount
     *
     * @return $this|CustomerInterface
     */
    public function setOutstandingBalance(float $amount): CustomerInterface;

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState(): string;

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|CustomerInterface
     */
    public function setState(string $state): CustomerInterface;

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription(): ?string;

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|CustomerInterface
     */
    public function setDescription(string $description = null): CustomerInterface;

    /**
     * Sets the locale
     *
     * @param string|null $locale
     *
     * @return $this|CustomerInterface
     */
    public function setLocale(string $locale = null): Resource\LocalizedInterface;

    /**
     * Returns the default invoice address.
     *
     * @param bool $allowParentAddress
     *
     * @return CustomerAddressInterface|null
     */
    public function getDefaultInvoiceAddress(bool $allowParentAddress = false): ?CustomerAddressInterface;

    /**
     * Returns the default delivery address.
     *
     * @param bool $allowParentAddress
     *
     * @return CustomerAddressInterface|null
     */
    public function getDefaultDeliveryAddress(bool $allowParentAddress = false): ?CustomerAddressInterface;
}

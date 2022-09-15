<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Entity\CustomerContact;
use Ekyna\Component\Commerce\Customer\Entity\CustomerLogo;
use Ekyna\Component\Commerce\Customer\Entity\CustomerPosition;
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
    NotificationsInterface,
    Payment\PaymentTermSubjectInterface,
    VatNumberSubjectInterface
{
    public function getCompany(): ?string;

    public function setCompany(?string $company): CustomerInterface;

    public function getCompanyNumber(): ?string;

    public function setCompanyNumber(?string $number): CustomerInterface;

    public function getEmail(): ?string;

    public function setEmail(?string $email): CustomerInterface;

    public function getPhone(): ?PhoneNumber;

    public function setPhone(?PhoneNumber $phone): CustomerInterface;

    public function getMobile(): ?PhoneNumber;

    public function setMobile(?PhoneNumber $mobile): CustomerInterface;

    public function getCustomerPosition(): ?CustomerPosition;

    public function setCustomerPosition(?CustomerPosition $position): CustomerInterface;

    public function getBirthday(): ?DateTimeInterface;

    public function setBirthday(?DateTimeInterface $birthday): CustomerInterface;

    public function hasParent(): bool;

    public function getParent(): ?CustomerInterface;

    public function setParent(?CustomerInterface $parent): CustomerInterface;

    /**
     * @return Collection<CustomerInterface>
     */
    public function getChildren(): Collection;

    public function hasChild(CustomerInterface $child): bool;

    public function addChild(CustomerInterface $child): CustomerInterface;

    public function removeChild(CustomerInterface $child): CustomerInterface;

    public function hasChildren(): bool;

    public function getCustomerGroup(): ?CustomerGroupInterface;

    public function setCustomerGroup(?CustomerGroupInterface $group): CustomerInterface;

    /**
     * @return Collection<CustomerAddressInterface>
     */
    public function getAddresses(): Collection;

    public function hasAddress(CustomerAddressInterface $address): bool;

    public function addAddress(CustomerAddressInterface $address): CustomerInterface;

    public function removeAddress(CustomerAddressInterface $address): CustomerInterface;

    /**
     * @return Collection<CustomerContact>
     */
    public function getContacts(): Collection;

    public function hasContact(CustomerContact $contact): bool;

    public function addContact(CustomerContact $contact): CustomerInterface;

    public function removeContact(CustomerContact $contact): CustomerInterface;

    public function getDefaultPaymentMethod(): ?Payment\PaymentMethodInterface;

    public function setDefaultPaymentMethod(?Payment\PaymentMethodInterface $method): CustomerInterface;

    /**
     * @return Collection<Payment\PaymentMethodInterface>
     */
    public function getPaymentMethods(): Collection;

    public function hasPaymentMethod(Payment\PaymentMethodInterface $method): bool;

    public function addPaymentMethod(Payment\PaymentMethodInterface $method): CustomerInterface;

    public function removePaymentMethod(Payment\PaymentMethodInterface $method): CustomerInterface;

    public function getBrandLogo(): ?CustomerLogo;

    public function setBrandLogo(?CustomerLogo $logo): CustomerInterface;

    public function getBrandColor(): ?string;

    public function setBrandColor(?string $color): CustomerInterface;

    public function getBrandUrl(): ?string;

    public function setBrandUrl(?string $url): CustomerInterface;

    public function getDocumentFooter(): ?string;

    public function setDocumentFooter(?string $html): CustomerInterface;

    /**
     * Returns the document types (logo and color usage).
     *
     * @return array<string>
     */
    public function getDocumentTypes(): array;

    /**
     * Sets the document types (logo and color usage).
     */
    public function setDocumentTypes(array $types): CustomerInterface;

    public function getLoyaltyPoints(): int;

    public function setLoyaltyPoints(int $points): CustomerInterface;

    public function getCreditBalance(): Decimal;

    public function setCreditBalance(Decimal $creditBalance): CustomerInterface;

    public function getOutstandingLimit(): Decimal;

    public function setOutstandingLimit(Decimal $limit): CustomerInterface;

    /**
     * Returns whether outstanding overflow is allowed (by setting custom limit on sales).
     */
    public function isOutstandingOverflow(): bool;

    public function setOutstandingOverflow(bool $overflow): CustomerInterface;

    public function getOutstandingBalance(): Decimal;

    public function setOutstandingBalance(Decimal $amount): CustomerInterface;

    public function getState(): string;

    public function setState(string $state): CustomerInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): CustomerInterface;

    public function setLocale(?string $locale): Resource\LocalizedInterface;

    public function getDefaultInvoiceAddress(bool $allowParentAddress = false): ?CustomerAddressInterface;

    public function getDefaultDeliveryAddress(bool $allowParentAddress = false): ?CustomerAddressInterface;
}

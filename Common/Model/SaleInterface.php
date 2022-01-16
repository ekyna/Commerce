<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermSubjectInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShippableInterface;
use Ekyna\Component\Resource\Model as Resource;

/**
 * Interface SaleInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Collection|SaleAdjustmentInterface[] getAdjustments(string $type = null)
 * @method Collection|SaleNotificationInterface[] getNotifications(string $type = null)
 */
interface SaleInterface extends
    Resource\ResourceInterface,
    Resource\TimestampableInterface,
    Resource\LocalizedInterface,
    IdentityInterface,
    AdjustableInterface,
    NotifiableInterface,
    NumberSubjectInterface,
    KeySubjectInterface,
    StateSubjectInterface,
    PaymentSubjectInterface,
    PaymentTermSubjectInterface,
    ShippableInterface,
    VatNumberSubjectInterface,
    Resource\RuntimeUidInterface
{
    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): SaleInterface;

    public function getCustomerGroup(): ?CustomerGroupInterface;

    public function setCustomerGroup(?CustomerGroupInterface $customerGroup): SaleInterface;

    public function getCompany(): ?string;

    public function setCompany(?string $company): SaleInterface;

    public function getCompanyNumber(): ?string;

    public function setCompanyNumber(?string $number): SaleInterface;

    public function getEmail(): ?string;

    public function setEmail(?string $email): SaleInterface;

    public function getInvoiceAddress(): ?SaleAddressInterface;

    public function setInvoiceAddress(?SaleAddressInterface $address): SaleInterface;

    public function getDeliveryAddress(): ?SaleAddressInterface;

    public function setDeliveryAddress(?SaleAddressInterface $address): SaleInterface;

    /**
     * Returns whether the invoice address is used as delivery address or not.
     */
    public function isSameAddress(): bool;

    /**
     * Sets whether to use the invoice address as delivery address or not.
     */
    public function setSameAddress(bool $same): SaleInterface;

    public function getCoupon(): ?CouponInterface;

    public function setCoupon(?CouponInterface $coupon): SaleInterface;

    public function getCouponData(): ?array;

    public function setCouponData(?array $data): SaleInterface;

    /**
     * Returns whether to generate discounts automatically.
     */
    public function isAutoDiscount(): bool;

    /**
     * Sets whether to generate discounts automatically.
     */
    public function setAutoDiscount(bool $auto): SaleInterface;

    public function isTaxExempt(): bool;

    public function setTaxExempt(bool $exempt): SaleInterface;

    public function getVatDisplayMode(): ?string;

    public function setVatDisplayMode(?string $mode): SaleInterface;

    /**
     * Returns whether prices should be displayed "all taxes included".
     */
    public function isAtiDisplayMode(): bool;

    /**
     * Returns whether the sale contains sample items.
     */
    public function isSample(): bool;

    /**
     * Returns whether the sale is released.
     */
    public function isReleased(): bool;

    public function getNetTotal(): Decimal;

    public function setNetTotal(Decimal $total): SaleInterface;

    public function getTitle(): ?string;

    public function setTitle(?string $title): SaleInterface;

    public function getVoucherNumber(): ?string;

    public function setVoucherNumber(?string $number): SaleInterface;

    public function getOriginNumber(): ?string;

    public function setOriginNumber(?string $number): SaleInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): SaleInterface;

    public function getPreparationNote(): ?string;

    public function setPreparationNote(?string $note): SaleInterface;

    public function getComment(): ?string;

    public function setComment(?string $comment): SaleInterface;

    public function getDocumentComment(): ?string;

    public function setDocumentComment(?string $comment): SaleInterface;

    public function getAcceptedAt(): ?DateTimeInterface;

    public function setAcceptedAt(?DateTimeInterface $date): SaleInterface;

    public function getSource(): string;

    public function setSource(string $source): SaleInterface;

    public function setLocale(?string $locale): Resource\LocalizedInterface;

    public function hasAttachments(): bool;

    public function hasAttachment(SaleAttachmentInterface $attachment): bool;

    public function addAttachment(SaleAttachmentInterface $attachment): SaleInterface;

    public function removeAttachment(SaleAttachmentInterface $attachment): SaleInterface;

    public function getAttachments(): Collection;

    public function hasItems(): bool;

    public function hasItem(SaleItemInterface $item): bool;

    public function addItem(SaleItemInterface $item): SaleInterface;

    public function removeItem(SaleItemInterface $item): SaleInterface;

    /**
     * @return Collection|SaleItemInterface[]
     */
    public function getItems(): Collection;

    public function getDeliveryCountry(): ?CountryInterface;

    public function setContext(ContextInterface $context): SaleInterface;

    public function getContext(): ?ContextInterface;

    public function isLocked(): bool;

    public function canBeReleased(): bool;

    public function hasDiscountItemAdjustment(): bool;
}

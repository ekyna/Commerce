<?php

namespace Ekyna\Component\Commerce\Common\Model;

use DateTime;
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
 * @method Collection|SaleAdjustmentInterface[] getAdjustments($type = null)
 * @method Collection|SaleNotificationInterface[] getNotifications($type = null)
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
    VatNumberSubjectInterface
{
    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer();

    /**
     * Sets the customer.
     *
     * @param CustomerInterface $customer
     *
     * @return $this|SaleInterface
     */
    public function setCustomer(CustomerInterface $customer = null);

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
     * @return $this|SaleInterface
     */
    public function setCustomerGroup(CustomerGroupInterface $customerGroup = null);

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
     * @return $this|SaleInterface
     */
    public function setCompany($company);

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
     * @return $this|SaleInterface
     */
    public function setCompanyNumber(string $number = null): SaleInterface;

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
     * @return $this|SaleInterface
     */
    public function setEmail($email);

    /**
     * Returns the invoice address.
     *
     * @return SaleAddressInterface
     */
    public function getInvoiceAddress();

    /**
     * Sets the invoice address.
     *
     * @param SaleAddressInterface $address
     *
     * @return $this|SaleInterface
     */
    public function setInvoiceAddress(SaleAddressInterface $address = null);

    /**
     * Returns the delivery address.
     *
     * @return SaleAddressInterface
     */
    public function getDeliveryAddress();

    /**
     * Sets the delivery address.
     *
     * @param SaleAddressInterface $address
     *
     * @return $this|SaleInterface
     */
    public function setDeliveryAddress(SaleAddressInterface $address = null);

    /**
     * Returns whether the invoice address is used as delivery address or not.
     *
     * @return boolean
     */
    public function isSameAddress();

    /**
     * Sets whether to use the invoice address as delivery address or not.
     *
     * @param boolean $same
     *
     * @return $this|SaleInterface
     */
    public function setSameAddress($same);

    /**
     * Returns the coupon.
     *
     * @return CouponInterface|null
     */
    public function getCoupon(): ?CouponInterface;

    /**
     * Sets the coupon.
     *
     * @param CouponInterface|null $coupon
     *
     * @return SaleInterface
     */
    public function setCoupon(CouponInterface $coupon = null): SaleInterface;

    /**
     * Returns the coupon data.
     *
     * @return array|null
     */
    public function getCouponData(): ?array;

    /**
     * Sets the coupon data.
     *
     * @param array|null $data
     *
     * @return SaleInterface
     */
    public function setCouponData(array $data = null): SaleInterface;

    /**
     * Returns whether to generate discounts automatically.
     *
     * @return bool
     */
    public function isAutoDiscount();

    /**
     * Sets whether to generate discounts automatically.
     *
     * @param bool $auto
     *
     * @return $this|SaleInterface
     */
    public function setAutoDiscount($auto);

    /**
     * Returns whether the sale is tax exempt.
     *
     * @return boolean
     */
    public function isTaxExempt();

    /**
     * Sets whether the sale is tax exempt.
     *
     * @param boolean $exempt
     *
     * @return $this|SaleInterface
     */
    public function setTaxExempt($exempt);

    /**
     * Returns the vat display mode.
     *
     * @return string
     */
    public function getVatDisplayMode();

    /**
     * Sets the vat display mode.
     *
     * @param string $mode
     *
     * @return $this|SaleInterface
     */
    public function setVatDisplayMode($mode);

    /**
     * Returns whether prices should be displayed "all taxes included".
     *
     * @return bool
     */
    public function isAtiDisplayMode();

    /**
     * Returns whether the sale contains sample items.
     *
     * @return bool
     */
    public function isSample();

    /**
     * Returns whether the sale is released.
     *
     * @return bool
     */
    public function isReleased();

    /**
     * Returns the net total.
     *
     * @return float
     */
    public function getNetTotal();

    /**
     * Sets the net total.
     *
     * @param float $total
     *
     * @return $this|SaleInterface
     */
    public function setNetTotal($total);

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return $this|SaleInterface
     */
    public function setTitle($title);

    /**
     * Returns the voucher number.
     *
     * @return string
     */
    public function getVoucherNumber();

    /**
     * Sets the voucher number.
     *
     * @param string $number
     *
     * @return $this|SaleInterface
     */
    public function setVoucherNumber($number);

    /**
     * Returns the origin number.
     *
     * @return string
     */
    public function getOriginNumber();

    /**
     * Sets the origin number.
     *
     * @param string $number
     *
     * @return $this|SaleInterface
     */
    public function setOriginNumber($number);

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
     * @return $this|SaleInterface
     */
    public function setDescription($description);

    /**
     * Returns the preparation note.
     *
     * @return string
     */
    public function getPreparationNote();

    /**
     * Sets the preparation note.
     *
     * @param string $note
     *
     * @return $this|SaleInterface
     */
    public function setPreparationNote($note);

    /**
     * Returns the comment.
     *
     * @return string
     */
    public function getComment();

    /**
     * Sets the comment.
     *
     * @param string $comment
     *
     * @return $this|SaleInterface
     */
    public function setComment($comment);

    /**
     * Returns the document comment.
     *
     * @return string
     */
    public function getDocumentComment();

    /**
     * Sets the document comment.
     *
     * @param string $comment
     *
     * @return $this|SaleInterface
     */
    public function setDocumentComment($comment);

    /**
     * Returns the "accepted at" datetime.
     *
     * @return DateTime
     */
    public function getAcceptedAt();

    /**
     * Sets the "accepted at" datetime.
     *
     * @param DateTime $acceptedAt
     *
     * @return $this|SaleInterface
     */
    public function setAcceptedAt(DateTime $acceptedAt = null);

    /**
     * Returns the source.
     *
     * @return string
     */
    public function getSource();

    /**
     * Sets the source.
     *
     * @param string $source
     *
     * @return $this|SaleInterface
     */
    public function setSource($source);

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return $this|SaleInterface
     */
    public function setLocale(string $locale = null): Resource\LocalizedInterface;

    /**
     * Returns whether the order has attachments or not.
     *
     * @return bool
     */
    public function hasAttachments();

    /**
     * Returns whether the order has the attachment or not.
     *
     * @param SaleAttachmentInterface $attachment
     *
     * @return bool
     */
    public function hasAttachment(SaleAttachmentInterface $attachment);

    /**
     * Adds the attachment.
     *
     * @param SaleAttachmentInterface $attachment
     *
     * @return $this|SaleInterface
     */
    public function addAttachment(SaleAttachmentInterface $attachment);

    /**
     * Removes the attachment.
     *
     * @param SaleAttachmentInterface $attachment
     *
     * @return $this|SaleInterface
     */
    public function removeAttachment(SaleAttachmentInterface $attachment);

    /**
     * Returns the attachments.
     *
     * @return Collection|SaleAttachmentInterface[]
     */
    public function getAttachments();

    /**
     * Returns whether or not the sale has at least one item.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns whether the sale has the item or not.
     *
     * @param SaleItemInterface $item
     *
     * @return bool
     */
    public function hasItem(SaleItemInterface $item);

    /**
     * Adds the item.
     *
     * @param SaleItemInterface $item
     *
     * @return $this|SaleInterface
     */
    public function addItem(SaleItemInterface $item);

    /**
     * Removes the item.
     *
     * @param SaleItemInterface $item
     *
     * @return $this|SaleInterface
     */
    public function removeItem(SaleItemInterface $item);

    /**
     * Returns the items.
     *
     * @return Collection|SaleItemInterface[]
     */
    public function getItems();

    /**
     * Returns the delivery country.
     *
     * @return CountryInterface|null
     */
    public function getDeliveryCountry();

    /**
     * Sets the context.
     *
     * @param ContextInterface $context
     *
     * @return $this|SaleInterface
     */
    public function setContext(ContextInterface $context): SaleInterface;

    /**
     * Returns the context.
     *
     * @return ContextInterface|null
     */
    public function getContext(): ?ContextInterface;

    /**
     * Returns whether the sale is locked.
     *
     * @return bool
     */
    public function isLocked(): bool;

    /**
     * Returns whether the sale can be released.
     *
     * @return bool
     */
    public function canBeReleased(): bool;

    /**
     * Returns whether at least one item has a discount adjustment.
     *
     * @return bool
     */
    public function hasDiscountItemAdjustment(): bool;
}

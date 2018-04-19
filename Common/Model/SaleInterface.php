<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Calculator\Amount;
use Ekyna\Component\Commerce\Common\Calculator\Margin;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermSubjectInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShippableInterface;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface SaleInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Collection|SaleAdjustmentInterface[] getAdjustments($type = null)
 */
interface SaleInterface extends
    ResourceModel\ResourceInterface,
    ResourceModel\TimestampableInterface,
    IdentityInterface,
    AdjustableInterface,
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
    public function setCustomer(CustomerInterface $customer);

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
    public function setCustomerGroup(CustomerGroupInterface $customerGroup);

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
     * Returns whether the sale contains sample items.
     *
     * @return bool
     */
    public function isSample();

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
     * Returns the adjustment total.
     *
     * @return float
     */
    public function getAdjustmentTotal();

    /**
     * Sets the adjustment total.
     *
     * @param float $total
     *
     * @return $this|SaleInterface
     */
    public function setAdjustmentTotal($total);

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
     * @return \DateTime
     */
    public function getAcceptedAt();

    /**
     * Sets the "accepted at" datetime.
     *
     * @param \DateTime $acceptedAt
     *
     * @return $this|SaleInterface
     */
    public function setAcceptedAt(\DateTime $acceptedAt = null);

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
     * Clears the results.
     *
     * @return $this|SaleInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function clearResults();

    /**
     * Sets the gross result.
     *
     * @param Amount $result
     *
     * @return $this|SaleInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function setGrossResult(Amount $result);

    /**
     * Returns the gross result.
     *
     * @return Amount
     *
     * @internal Usage reserved to view builder.
     */
    public function getGrossResult();

    /**
     * Sets the shipment result.
     *
     * @param Amount $result
     *
     * @return $this|SaleInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function setShipmentResult(Amount $result);

    /**
     * Returns the shipment result.
     *
     * @return Amount|null
     *
     * @internal Usage reserved to view builder.
     */
    public function getShipmentResult();

    /**
     * Sets the final result.
     *
     * @param Amount $result
     *
     * @return $this|SaleInterface
     *
     * @internal Usage reserved to calculator.
     */
    public function setFinalResult(Amount $result);

    /**
     * Returns the final result.
     *
     * @return Amount
     *
     * @internal Usage reserved to view builder.
     */
    public function getFinalResult();

    /**
     * Sets the margin.
     *
     * @param Margin $margin
     */
    public function setMargin(Margin $margin);

    /**
     * Returns the margin.
     *
     * @return Margin
     */
    public function getMargin();
}

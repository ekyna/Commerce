<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface SaleInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleInterface extends
    ResourceModel\ResourceInterface,
    ResourceModel\TimestampableInterface,
    IdentityInterface,
    AdjustableInterface,
    NumberSubjectInterface,
    KeySubjectInterface,
    StateSubjectInterface,
    CurrencySubjectInterface,
    PaymentTermSubjectInterface
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
     * @return AddressInterface
     */
    public function getInvoiceAddress();

    /**
     * Sets the invoice address.
     *
     * @param AddressInterface $address
     *
     * @return $this|SaleInterface
     */
    public function setInvoiceAddress(AddressInterface $address = null);

    /**
     * Returns the delivery address.
     *
     * @return AddressInterface
     */
    public function getDeliveryAddress();

    /**
     * Sets the delivery address.
     *
     * @param AddressInterface $address
     *
     * @return $this|SaleInterface
     */
    public function setDeliveryAddress(AddressInterface $address = null);

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
     * Returns the preferred shipment method.
     *
     * @return ShipmentMethodInterface
     */
    public function getPreferredShipmentMethod();

    /**
     * Sets the preferred shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|SaleInterface
     */
    public function setPreferredShipmentMethod(ShipmentMethodInterface $method = null);

    /**
     * Returns whether the sale is tax exempt.
     *
     * @return boolean
     */
    public function isTaxExempt();

    /**
     * Sets the tax exempt.
     *
     * @param boolean $exempt
     *
     * @return $this|SaleInterface
     */
    public function setTaxExempt($exempt);

    /**
     * Returns the weight total (kilograms).
     *
     * @return float
     */
    public function getWeightTotal();

    /**
     * Sets the weight total (kilograms).
     *
     * @param float $total
     *
     * @return $this|SaleInterface
     */
    public function setWeightTotal($total);

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
     * Returns the shipment amount.
     *
     * @return float
     */
    public function getShipmentAmount();

    /**
     * Sets the shipment amount.
     *
     * @param float $amount
     *
     * @return $this|SaleInterface
     */
    public function setShipmentAmount($amount);

    /**
     * Returns the total.
     *
     * @return float
     */
    public function getGrandTotal();

    /**
     * Sets the total.
     *
     * @param float $total
     *
     * @return $this|SaleInterface
     */
    public function setGrandTotal($total);

    /**
     * Returns the paid total.
     *
     * @return float
     */
    public function getPaidTotal();

    /**
     * Sets the paid total.
     *
     * @param float $total
     *
     * @return $this|SaleInterface
     */
    public function setPaidTotal($total);

    /**
     * Returns the outstanding limit.
     *
     * @return float
     */
    public function getOutstandingLimit();

    /**
     * Sets the outstanding amount.
     *
     * @param float $amount
     *
     * @return $this|SaleInterface
     */
    public function setOutstandingLimit($amount);

    /**
     * Returns the outstanding date.
     *
     * @return \DateTime
     */
    public function getOutstandingDate();

    /**
     * Sets the outstanding date.
     *
     * @param \DateTime $date
     *
     * @return $this|SaleInterface
     */
    public function setOutstandingDate(\DateTime $date = null);

    /**
     * Returns the payment state.
     *
     * @return string
     */
    public function getPaymentState();

    /**
     * Sets the payment state.
     *
     * @param string $state
     *
     * @return $this|SaleInterface
     */
    public function setPaymentState($state);


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
     * Returns the customer attachments.
     *
     * @return Collection|SaleAttachmentInterface[]
     */
    public function getCustomerAttachments();

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
     * Returns whether the order has payments or not.
     *
     * @return bool
     */
    public function hasPayments();

    /**
     * Returns whether the order has the payment or not.
     *
     * @param PaymentInterface $payment
     *
     * @return bool
     */
    public function hasPayment(PaymentInterface $payment);

    /**
     * Adds the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return $this|SaleInterface
     */
    public function addPayment(PaymentInterface $payment);

    /**
     * Removes the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return $this|SaleInterface
     */
    public function removePayment(PaymentInterface $payment);

    /**
     * Returns the payments.
     *
     * @return Collection|PaymentInterface[]
     */
    public function getPayments();

    /**
     * Returns the payment remaining amount.
     *
     * @return float
     */
    public function getRemainingAmount();

    /**
     * Returns whether or not the sale has a weight greater that zero.
     *
     * @return bool
     */
    public function requiresShipment();
}

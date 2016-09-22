<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SaleInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleInterface extends
    ResourceInterface,
    AdjustableInterface,
    NumberSubjectInterface,
    KeySubjectInterface,
    StateSubjectInterface
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
     * Returns the firstName.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Sets the firstName.
     *
     * @param string $firstName
     *
     * @return $this|SaleInterface
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
     *
     * @return $this|SaleInterface
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
    public function setInvoiceAddress(AddressInterface $address);

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
    public function getSameAddress();

    /**
     * Sets whether to use the invoice address as delivery address or not.
     *
     * @param boolean $same
     *
     * @return $this|SaleInterface
     */
    public function setSameAddress($same);

    /**
     * Returns the currency.
     *
     * @return CurrencyInterface
     */
    public function getCurrency();

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|SaleInterface
     */
    public function setCurrency(CurrencyInterface $currency);

    /**
     * Returns the weight total.
     *
     * @return float
     */
    public function getWeightTotal();

    /**
     * Sets the weight total.
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
     * Returns the payment state.
     *
     * @return string
     */
    public function getPaymentState();

    /**
     * Sets the payment state.
     *
     * @param string $state
     * @return $this|SaleInterface
     */
    public function setPaymentState($state);

    /**
     * Returns whether the sale has items or not.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns whether the sale has the item or not.
     *
     * @param SaleItemInterface $item
     * @return bool
     */
    public function hasItem(SaleItemInterface $item);

    /**
     * Adds the item.
     *
     * @param SaleItemInterface $item
     * @return $this|SaleInterface
     */
    public function addItem(SaleItemInterface $item);

    /**
     * Removes the item.
     *
     * @param SaleItemInterface $item
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
     * @return bool
     */
    public function hasPayment(PaymentInterface $payment);

    /**
     * Adds the payment.
     *
     * @param PaymentInterface $payment
     * @return $this|SaleInterface
     */
    public function addPayment(PaymentInterface $payment);

    /**
     * Removes the payment.
     *
     * @param PaymentInterface $payment
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
     * @return $this|SaleInterface
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
     * @return $this|SaleInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);
}

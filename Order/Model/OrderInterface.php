<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Pricing\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the key.
     *
     * @return string
     */
    public function getKey();

    /**
     * Sets the key.
     *
     * @param string $key
     *
     * @return $this|OrderInterface
     */
    public function setKey($key);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getNumber();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|OrderInterface
     */
    public function setNumber($reference);

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
     * @return $this|OrderInterface
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
     * @return $this|OrderInterface
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
     * @return $this|OrderInterface
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
     * @return $this|OrderInterface
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
     * @return $this|OrderInterface
     */
    public function setEmail($email);

    /**
     * Returns the invoiceAddress.
     *
     * @return OrderAddressInterface
     */
    public function getInvoiceAddress();

    /**
     * Sets the invoiceAddress.
     *
     * @param OrderAddressInterface $invoiceAddress
     *
     * @return $this|OrderInterface
     */
    public function setInvoiceAddress(OrderAddressInterface $invoiceAddress);

    /**
     * Returns the deliveryAddress.
     *
     * @return OrderAddressInterface
     */
    public function getDeliveryAddress();

    /**
     * Sets the deliveryAddress.
     *
     * @param OrderAddressInterface $deliveryAddress
     *
     * @return $this|OrderInterface
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null);

    /**
     * Returns whether the invoice address is used as delivery address or not.
     *
     * @return boolean
     */
    public function getSameAddress();

    /**
     * Sets whether to use the invoice address as delivery address or not.
     *
     * @param boolean $sameAddress
     *
     * @return $this|OrderInterface
     */
    public function setSameAddress($sameAddress);

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
     * @return $this|OrderInterface
     */
    public function setCurrency(CurrencyInterface $currency);

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
     * @return $this|OrderInterface
     */
    public function setState($state);

    /**
     * Returns the payment state.
     *
     * @return string
     */
    public function getPaymentState();

    /**
     * Sets the payment state.
     *
     * @param string $paymentState
     * @return $this|OrderInterface
     */
    public function setPaymentState($paymentState);

    /**
     * Returns the shipment state.
     *
     * @return string
     */
    public function getShipmentState();

    /**
     * Sets the shipment state.
     *
     * @param string $shipmentState
     * @return $this|OrderInterface
     */
    public function setShipmentState($shipmentState);

    /**
     * Returns the weight total.
     *
     * @return float
     */
    public function getWeightTotal();

    /**
     * Sets the weight total.
     *
     * @param float $weightTotal
     *
     * @return $this|OrderInterface
     */
    public function setWeightTotal($weightTotal);

    /**
     * Returns the net total.
     *
     * @return float
     */
    public function getNetTotal();

    /**
     * Sets the net total.
     *
     * @param float $netTotal
     *
     * @return $this|OrderInterface
     */
    public function setNetTotal($netTotal);

    /**
     * Returns the tax total.
     *
     * @return float
     */
    public function getTaxTotal();

    /**
     * Sets the tax total.
     *
     * @param float $taxTotal
     *
     * @return $this|OrderInterface
     */
    public function setTaxTotal($taxTotal);

    /**
     * Returns the adjustment total.
     *
     * @return float
     */
    public function getAdjustmentTotal();

    /**
     * Sets the adjustment total.
     *
     * @param float $adjustmentTotal
     *
     * @return $this|OrderInterface
     */
    public function setAdjustmentTotal($adjustmentTotal);

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
     * @return $this|OrderInterface
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
     * @param float $paidTotal
     *
     * @return $this|OrderInterface
     */
    public function setPaidTotal($paidTotal);

    /**
     * Returns whether the order has items or not.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns the items.
     *
     * @return ArrayCollection|OrderItemInterface[]
     */
    public function getItems();

    /**
     * Returns whether the order has the item or not.
     *
     * @param OrderItemInterface $item
     * @return bool
     */
    public function hasItem(OrderItemInterface $item);

    /**
     * Adds the item.
     *
     * @param OrderItemInterface $item
     * @return $this|OrderInterface
     */
    public function addItem(OrderItemInterface $item);

    /**
     * Removes the item.
     *
     * @param OrderItemInterface $item
     * @return $this|OrderInterface
     */
    public function removeItem(OrderItemInterface $item);

    /**
     * Sets the items.
     *
     * @param ArrayCollection|OrderItemInterface[] $items
     * @return $this|OrderInterface
     */
    public function setItems(ArrayCollection $items);

    /**
     * Returns whether the order has adjustments or not.
     *
     * @return bool
     */
    public function hasAdjustments();

    /**
     * Returns the adjustments.
     *
     * @return ArrayCollection|OrderAdjustmentInterface[]
     */
    public function getAdjustments();

    /**
     * Returns whether the order has the adjustment or not.
     *
     * @param OrderAdjustmentInterface $adjustment
     * @return bool
     */
    public function hasAdjustment(OrderAdjustmentInterface $adjustment);

    /**
     * Adds the adjustment.
     *
     * @param OrderAdjustmentInterface $adjustment
     * @return $this|OrderInterface
     */
    public function addAdjustment(OrderAdjustmentInterface $adjustment);

    /**
     * Removes the adjustment.
     *
     * @param OrderAdjustmentInterface $adjustment
     * @return $this|OrderInterface
     */
    public function removeAdjustment(OrderAdjustmentInterface $adjustment);

    /**
     * Sets the adjustments.
     *
     * @param ArrayCollection|OrderAdjustmentInterface[] $adjustments
     * @return $this|OrderInterface
     */
    public function setAdjustments(ArrayCollection $adjustments);

    /**
     * Returns whether the order has payments or not.
     *
     * @return bool
     */
    public function hasPayments();

    /**
     * Returns the payments.
     *
     * @return ArrayCollection|PaymentInterface[]
     */
    public function getPayments();

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
     * @return $this|OrderInterface
     */
    public function addPayment(PaymentInterface $payment);

    /**
     * Removes the payment.
     *
     * @param PaymentInterface $payment
     * @return $this|OrderInterface
     */
    public function removePayment(PaymentInterface $payment);

    /**
     * Sets the payments.
     *
     * @param ArrayCollection|PaymentInterface[] $payments
     * @return $this|OrderInterface
     */
    public function setPayments(ArrayCollection $payments);

    /**
     * Returns whether the order has shipments or not.
     *
     * @return bool
     */
    public function hasShipments();

    /**
     * Returns the shipments.
     *
     * @return ArrayCollection|ShipmentInterface[]
     */
    public function getShipments();

    /**
     * Returns whether the order has the shipment or not.
     *
     * @param ShipmentInterface $shipment
     * @return bool
     */
    public function hasShipment(ShipmentInterface $shipment);

    /**
     * Adds the shipment.
     *
     * @param ShipmentInterface $shipment
     * @return $this|OrderInterface
     */
    public function addShipment(ShipmentInterface $shipment);

    /**
     * Removes the shipment.
     *
     * @param ShipmentInterface $shipment
     * @return $this|OrderInterface
     */
    public function removeShipment(ShipmentInterface $shipment);

    /**
     * Sets the shipments.
     *
     * @param ArrayCollection|ShipmentInterface[] $shipments
     * @return $this|OrderInterface
     */
    public function setShipments(ArrayCollection $shipments);

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
     * @return $this|OrderInterface
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
     * @return $this|OrderInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Returns the "completed at" datetime.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the "completed at" datetime.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|OrderInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}

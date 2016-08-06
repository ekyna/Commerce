<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderAddressInterface;
use Ekyna\Component\Commerce\Order\Model\OrderAdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class Order
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Order implements OrderInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var OrderAddressInterface
     */
    protected $invoiceAddress;

    /**
     * @var OrderAddressInterface
     */
    protected $deliveryAddress;

    /**
     * @var bool
     */
    protected $sameAddress;

    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $paymentState;

    /**
     * @var string
     */
    protected $shipmentState;

    /**
     * @var float
     */
    protected $weightTotal;

    /**
     * @var float
     */
    protected $netTotal;

    /**
     * @var float
     */
    protected $taxTotal;

    /**
     * @var float
     */
    protected $adjustmentTotal;

    /**
     * @var float
     */
    protected $grandTotal;

    /**
     * @var float
     */
    protected $paidTotal;

    /**
     * @var ArrayCollection|OrderItemInterface[]
     */
    protected $items;

    /**
     * @var ArrayCollection|OrderAdjustmentInterface[]
     */
    protected $adjustments;

    /**
     * @var ArrayCollection|PaymentInterface[]
     */
    protected $payments;

    /**
     * @var ArrayCollection|ShipmentInterface[]
     */
    protected $shipments;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $completedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sameAddress = false;

        $this->state = OrderStates::STATE_NEW;
        $this->paymentState = PaymentStates::STATE_NEW;
        $this->shipmentState = ShipmentStates::STATE_PENDING;

        $this->weightTotal = 0;
        $this->netTotal = 0;
        $this->taxTotal = 0;
        $this->adjustmentTotal = 0;
        $this->grandTotal = 0;
        $this->paidTotal = 0;

        $this->items = new ArrayCollection();
        $this->adjustments = new ArrayCollection();

        $this->payments = new ArrayCollection();
        $this->shipments = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getNumber();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @inheritdoc
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @inheritdoc
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @inheritdoc
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @inheritdoc
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @inheritdoc
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceAddress(OrderAddressInterface $invoiceAddress)
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null)
    {
        // TODO remove from database if null ?
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSameAddress()
    {
        return $this->sameAddress;
    }

    /**
     * @inheritdoc
     */
    public function setSameAddress($sameAddress)
    {
        $this->sameAddress = (bool)$sameAddress;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(CurrencyInterface $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentState($paymentState)
    {
        $this->paymentState = $paymentState;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentState()
    {
        return $this->paymentState;
    }

    /**
     * {@inheritdoc}
     */
    public function setShipmentState($shipmentState)
    {
        $this->shipmentState = $shipmentState;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShipmentState()
    {
        return $this->shipmentState;
    }

    /**
     * @inheritdoc
     */
    public function getWeightTotal()
    {
        return $this->weightTotal;
    }

    /**
     * @inheritdoc
     */
    public function setWeightTotal($weightTotal)
    {
        $this->weightTotal = $weightTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetTotal()
    {
        return $this->netTotal;
    }

    /**
     * @inheritdoc
     */
    public function setNetTotal($netTotal)
    {
        $this->netTotal = $netTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxTotal()
    {
        return $this->taxTotal;
    }

    /**
     * @inheritdoc
     */
    public function setTaxTotal($taxTotal)
    {
        $this->taxTotal = $taxTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustmentTotal()
    {
        return $this->adjustmentTotal;
    }

    /**
     * @inheritdoc
     */
    public function setAdjustmentTotal($adjustmentTotal)
    {
        $this->adjustmentTotal = $adjustmentTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGrandTotal()
    {
        return $this->grandTotal;
    }

    /**
     * @inheritdoc
     */
    public function setGrandTotal($grandTotal)
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaidTotal()
    {
        return $this->paidTotal;
    }

    /**
     * @inheritdoc
     */
    public function setPaidTotal($paidTotal)
    {
        $this->paidTotal = $paidTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItems()
    {
        return 0 < $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(OrderItemInterface $item)
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(OrderItemInterface $item)
    {
        if (!$this->hasItem($item)) {
            $item->setOrder($this);
            $this->items->add($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(OrderItemInterface $item)
    {
        if ($this->hasItem($item)) {
            $item->setOrder(null);
            $this->items->removeElement($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setItems(ArrayCollection $items)
    {
        foreach ($this->items as $item) {
            $item->setOrder(null);
        }

        $this->items = new ArrayCollection();

        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustments()
    {
        return 0 < $this->adjustments->count();
    }

    /**
     * @inheritdoc
     */
    public function getAdjustments()
    {
        return $this->adjustments;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(OrderAdjustmentInterface $adjustment)
    {
        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(OrderAdjustmentInterface $adjustment)
    {
        if (!$this->hasAdjustment($adjustment)) {
            $adjustment->setOrder($this);
            $this->adjustments->add($adjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(OrderAdjustmentInterface $adjustment)
    {
        if ($this->hasAdjustment($adjustment)) {
            $adjustment->setOrder(null);
            $this->adjustments->removeElement($adjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAdjustments(ArrayCollection $adjustments)
    {
        foreach ($this->adjustments as $adjustment) {
            $adjustment->setOrder(null);
        }

        $this->adjustments = new ArrayCollection();

        foreach ($adjustments as $adjustment) {
            $this->addAdjustment($adjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasPayments()
    {
        return 0 < $this->payments->count();
    }

    /**
     * @inheritdoc
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @inheritdoc
     */
    public function hasPayment(PaymentInterface $payment)
    {
        return $this->payments->contains($payment);
    }

    /**
     * @inheritdoc
     */
    public function addPayment(PaymentInterface $payment)
    {
        if (!$this->hasPayment($payment)) {
            $payment->setOrder($this);
            $this->payments->add($payment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePayment(PaymentInterface $payment)
    {
        if ($this->hasPayment($payment)) {
            $payment->setOrder(null);
            $this->payments->removeElement($payment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPayments(ArrayCollection $payments)
    {
        foreach ($this->payments as $payment) {
            $payment->setOrder(null);
        }

        $this->payments = new ArrayCollection();

        foreach ($payments as $payment) {
            $this->addPayment($payment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasShipments()
    {
        return 0 < $this->shipments->count();
    }

    /**
     * @inheritdoc
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * @inheritdoc
     */
    public function hasShipment(ShipmentInterface $shipment)
    {
        return $this->shipments->contains($shipment);
    }

    /**
     * @inheritdoc
     */
    public function addShipment(ShipmentInterface $shipment)
    {
        if (!$this->hasShipment($shipment)) {
            $shipment->setOrder($this);
            $this->shipments->add($shipment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeShipment(ShipmentInterface $shipment)
    {
        if ($this->hasShipment($shipment)) {
            $shipment->setOrder(null);
            $this->shipments->removeElement($shipment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setShipments(ArrayCollection $shipments)
    {
        foreach ($this->shipments as $shipment) {
            $shipment->setOrder(null);
        }

        $this->shipments = new ArrayCollection();

        foreach ($shipments as $shipment) {
            $this->addShipment($shipment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @inheritdoc
     */
    public function setCompletedAt(\DateTime $completedAt = null)
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}

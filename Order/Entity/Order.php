<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class Order
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Order extends AbstractSale implements Model\OrderInterface
{
    use Shipment\ShipmentSubjectTrait;
    use Invoice\InvoiceSubjectTrait;

    /**
     * @var bool
     */
    protected $sample;

    /**
     * @var bool
     */
    protected $released;

    /**
     * @var bool
     */
    protected $first;

    /**
     * @var CustomerInterface
     */
    protected $originCustomer;

    /**
     * @var \DateTime
     */
    protected $completedAt;

    /**
     * @var float
     */
    protected $marginTotal;

    /**
     * @var int
     */
    protected $itemsCount;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initializeShipmentSubject();
        $this->initializeInvoiceSubject();

        $this->state = Model\OrderStates::STATE_NEW;
        $this->source = Common\SaleSources::SOURCE_COMMERCIAL;
        $this->sample = false;
        $this->released = false;
        $this->first = false;

        $this->marginTotal = 0;
        $this->itemsCount = 0;
    }

    /**
     * @inheritdoc
     */
    public function isSample()
    {
        return $this->sample;
    }

    /**
     * @inheritdoc
     */
    public function setSample($sample)
    {
        $this->sample = (bool)$sample;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * @inheritdoc
     */
    public function setReleased($released)
    {
        $this->released = (bool)$released;

        return $this;
    }

    /**
     * Returns the first.
     *
     * @return bool
     */
    public function isFirst()
    {
        return $this->first;
    }

    /**
     * Sets the first.
     *
     * @param bool $first
     *
     * @return Order
     */
    public function setFirst($first)
    {
        $this->first = (bool)$first;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOriginCustomer()
    {
        return $this->originCustomer;
    }

    /**
     * @inheritdoc
     */
    public function setOriginCustomer(CustomerInterface $customer = null)
    {
        $this->originCustomer = $customer;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Model\OrderAddressInterface
     */
    public function getInvoiceAddress()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->invoiceAddress;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceAddress(Common\SaleAddressInterface $address = null)
    {
        if (null !== $address & !$address instanceof Model\OrderAddressInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderAddressInterface::class);
        }

        if ($address !== $current = $this->getInvoiceAddress()) {
            if (null !== $current) {
                $current->setInvoiceOrder(null);
            }

            $this->invoiceAddress = $address;

            if (null !== $address) {
                $address->setInvoiceOrder($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Model\OrderAddressInterface
     */
    public function getDeliveryAddress()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->deliveryAddress;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryAddress(Common\SaleAddressInterface $address = null)
    {
        if (null !== $address && !$address instanceof Model\OrderAddressInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderAddressInterface::class);
        }

        if ($address !== $current = $this->getDeliveryAddress()) {
            if (null !== $current) {
                $current->setDeliveryOrder(null);
            }

            $this->deliveryAddress = $address;

            if (null !== $address) {
                $address->setDeliveryOrder($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderAttachmentInterface::class);
        }

        return $this->attachments->contains($attachment);
    }

    /**
     * @inheritdoc
     */
    public function addAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderAttachmentInterface::class);
        }

        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderAttachmentInterface::class);
        }

        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderItemInterface::class);
        }

        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderItemInterface::class);
        }

        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderItemInterface::class);
        }

        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderAdjustmentInterface::class);
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderAdjustmentInterface::class);
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderAdjustmentInterface::class);
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasNotification(Common\NotificationInterface $notification)
    {
        if (!$notification instanceof Model\OrderNotificationInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderNotificationInterface::class);
        }

        return $this->notifications->contains($notification);
    }

    /**
     * @inheritdoc
     */
    public function addNotification(Common\NotificationInterface $notification)
    {
        if (!$notification instanceof Model\OrderNotificationInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderNotificationInterface::class);
        }

        if (!$this->hasNotification($notification)) {
            $this->notifications->add($notification);
            $notification->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeNotification(Common\NotificationInterface $notification)
    {
        if (!$notification instanceof Model\OrderNotificationInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderNotificationInterface::class);
        }

        if ($this->hasNotification($notification)) {
            $this->notifications->removeElement($notification);
            $notification->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasPayment(Payment\PaymentInterface $payment): bool
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderPaymentInterface::class);
        }

        return $this->payments->contains($payment);
    }

    /**
     * @inheritdoc
     */
    public function addPayment(Payment\PaymentInterface $payment): Payment\PaymentSubjectInterface
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderPaymentInterface::class);
        }

        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePayment(Payment\PaymentInterface $payment): Payment\PaymentSubjectInterface
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderPaymentInterface::class);
        }

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasShipment(Shipment\ShipmentInterface $shipment)
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderShipmentInterface::class);
        }

        return $this->shipments->contains($shipment);
    }

    /**
     * @inheritdoc
     */
    public function addShipment(Shipment\ShipmentInterface $shipment)
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderShipmentInterface::class);
        }

        if (!$this->hasShipment($shipment)) {
            $this->shipments->add($shipment);
            $shipment->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeShipment(Shipment\ShipmentInterface $shipment)
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderShipmentInterface::class);
        }

        if ($this->hasShipment($shipment)) {
            $this->shipments->removeElement($shipment);
            $shipment->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasInvoice(Invoice\InvoiceInterface $invoice)
    {
        if (!$invoice instanceof Model\OrderInvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderInvoiceInterface::class);
        }

        return $this->invoices->contains($invoice);
    }

    /**
     * @inheritdoc
     */
    public function addInvoice(Invoice\InvoiceInterface $invoice)
    {
        if (!$invoice instanceof Model\OrderInvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderInvoiceInterface::class);
        }

        if (!$this->hasInvoice($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeInvoice(Invoice\InvoiceInterface $invoice)
    {
        if (!$invoice instanceof Model\OrderInvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderInvoiceInterface::class);
        }

        if ($this->hasInvoice($invoice)) {
            $this->invoices->removeElement($invoice);
            $invoice->setOrder(null);
        }

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

    /**
     * @inheritdoc
     */
    public function getMarginTotal()
    {
        return $this->marginTotal;
    }

    /**
     * @inheritdoc
     */
    public function setMarginTotal($amount)
    {
        $this->marginTotal = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    /**
     * @inheritdoc
     */
    public function setItemsCount($count)
    {
        $this->itemsCount = (int)$count;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function canBeReleased(): bool
    {
        if (!$this->isSample()) {
            return false;
        }

        if ($this->isReleased()) {
            return false;
        }

        // A sample order needs at least and received return to be release ready.
        foreach ($this->getShipments() as $shipment) {
            if ($shipment->isReturn() && Shipment\ShipmentStates::isStockableState($shipment->getState())) {
                return true;
            }
        }

        return false;
    }
}

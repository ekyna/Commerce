<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
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

    protected bool               $sample         = false;
    protected bool               $released       = false;
    protected bool               $first          = false;
    protected ?CustomerInterface $originCustomer = null;
    protected ?DateTimeInterface $completedAt    = null;
    protected ?Decimal           $revenueTotal   = null;
    protected ?Decimal           $marginTotal    = null;
    protected int                $itemsCount     = 0;


    public function __construct()
    {
        parent::__construct();

        $this->initializeShipmentSubject();
        $this->initializeInvoiceSubject();

        $this->state = Model\OrderStates::STATE_NEW;
        $this->source = Common\SaleSources::SOURCE_COMMERCIAL;
    }

    public function isSample(): bool
    {
        return $this->sample;
    }

    public function setSample(bool $sample): Model\OrderInterface
    {
        $this->sample = $sample;

        return $this;
    }

    public function isReleased(): bool
    {
        return $this->released;
    }

    public function setReleased(bool $released): Model\OrderInterface
    {
        $this->released = $released;

        return $this;
    }

    public function isFirst(): bool
    {
        return $this->first;
    }

    public function setFirst(bool $first): Model\OrderInterface
    {
        $this->first = $first;

        return $this;
    }

    public function getOriginCustomer(): ?CustomerInterface
    {
        return $this->originCustomer;
    }

    public function setOriginCustomer(?CustomerInterface $customer): Model\OrderInterface
    {
        $this->originCustomer = $customer;

        return $this;
    }

    /**
     * @return Model\OrderAddressInterface|null
     */
    public function getInvoiceAddress(): ?Common\SaleAddressInterface
    {
        return $this->invoiceAddress;
    }

    public function setInvoiceAddress(?Common\SaleAddressInterface $address): Common\SaleInterface
    {
        if ($address && !$address instanceof Model\OrderAddressInterface) {
            throw new UnexpectedTypeException($address, Model\OrderAddressInterface::class);
        }

        if ($address === $this->invoiceAddress) {
            return $this;
        }

        if ($previous = $this->invoiceAddress) {
            $this->invoiceAddress = null;
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $previous->setInvoiceOrder(null);
        }

        if ($this->invoiceAddress = $address) {
            $address->setInvoiceOrder($this);
        }

        return $this;
    }

    /**
     * @return Model\OrderAddressInterface|null
     */
    public function getDeliveryAddress(): ?Common\SaleAddressInterface
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Common\SaleAddressInterface $address): Common\SaleInterface
    {
        if ($address && !$address instanceof Model\OrderAddressInterface) {
            throw new UnexpectedTypeException($address, Model\OrderAddressInterface::class);
        }

        if ($address === $this->deliveryAddress) {
            return $this;
        }

        if ($previous = $this->deliveryAddress) {
            $this->deliveryAddress = null;
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $previous->setDeliveryOrder(null);
        }

        if ($this->deliveryAddress = $address) {
            $address->setDeliveryOrder($this);
        }

        return $this;
    }

    public function hasAttachment(Common\SaleAttachmentInterface $attachment): bool
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\OrderAttachmentInterface::class);
        }

        return $this->attachments->contains($attachment);
    }

    public function addAttachment(Common\SaleAttachmentInterface $attachment): Common\SaleInterface
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\OrderAttachmentInterface::class);
        }

        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setOrder($this);
        }

        return $this;
    }

    public function removeAttachment(Common\SaleAttachmentInterface $attachment): Common\SaleInterface
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\OrderAttachmentInterface::class);
        }

        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setOrder(null);
        }

        return $this;
    }

    public function hasItem(Common\SaleItemInterface $item): bool
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new UnexpectedTypeException($item, Model\OrderItemInterface::class);
        }

        return $this->items->contains($item);
    }

    public function addItem(Common\SaleItemInterface $item): Common\SaleInterface
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new UnexpectedTypeException($item, Model\OrderItemInterface::class);
        }

        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(Common\SaleItemInterface $item): Common\SaleInterface
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new UnexpectedTypeException($item, Model\OrderItemInterface::class);
        }

        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setOrder(null);
        }

        return $this;
    }

    public function hasAdjustment(Common\AdjustmentInterface $adjustment): bool
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\OrderAdjustmentInterface::class);
        }

        return $this->adjustments->contains($adjustment);
    }

    public function addAdjustment(Common\AdjustmentInterface $adjustment): Common\AdjustableInterface
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\OrderAdjustmentInterface::class);
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setOrder($this);
        }

        return $this;
    }

    public function removeAdjustment(Common\AdjustmentInterface $adjustment): Common\AdjustableInterface
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\OrderAdjustmentInterface::class);
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setOrder(null);
        }

        return $this;
    }

    public function hasNotification(Common\NotificationInterface $notification): bool
    {
        if (!$notification instanceof Model\OrderNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\OrderNotificationInterface::class);
        }

        return $this->notifications->contains($notification);
    }

    public function addNotification(Common\NotificationInterface $notification): Common\NotifiableInterface
    {
        if (!$notification instanceof Model\OrderNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\OrderNotificationInterface::class);
        }

        if (!$this->hasNotification($notification)) {
            $this->notifications->add($notification);
            $notification->setOrder($this);
        }

        return $this;
    }

    public function removeNotification(Common\NotificationInterface $notification): Common\NotifiableInterface
    {
        if (!$notification instanceof Model\OrderNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\OrderNotificationInterface::class);
        }

        if ($this->hasNotification($notification)) {
            $this->notifications->removeElement($notification);
            $notification->setOrder(null);
        }

        return $this;
    }

    public function hasPayment(Payment\PaymentInterface $payment): bool
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\OrderPaymentInterface::class);
        }

        return $this->payments->contains($payment);
    }

    public function addPayment(Payment\PaymentInterface $payment): Payment\PaymentSubjectInterface
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\OrderPaymentInterface::class);
        }

        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setOrder($this);
        }

        return $this;
    }

    public function removePayment(Payment\PaymentInterface $payment): Payment\PaymentSubjectInterface
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\OrderPaymentInterface::class);
        }

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setOrder(null);
        }

        return $this;
    }

    public function hasShipment(Shipment\ShipmentInterface $shipment): bool
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new UnexpectedTypeException($shipment, Model\OrderShipmentInterface::class);
        }

        return $this->shipments->contains($shipment);
    }

    public function addShipment(Shipment\ShipmentInterface $shipment): Shipment\ShipmentSubjectInterface
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new UnexpectedTypeException($shipment, Model\OrderShipmentInterface::class);
        }

        if (!$this->hasShipment($shipment)) {
            $this->shipments->add($shipment);
            $shipment->setOrder($this);
        }

        return $this;
    }

    public function removeShipment(Shipment\ShipmentInterface $shipment): Shipment\ShipmentSubjectInterface
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new UnexpectedTypeException($shipment, Model\OrderShipmentInterface::class);
        }

        if ($this->hasShipment($shipment)) {
            $this->shipments->removeElement($shipment);
            $shipment->setOrder(null);
        }

        return $this;
    }

    public function hasInvoice(Invoice\InvoiceInterface $invoice): bool
    {
        if (!$invoice instanceof Model\OrderInvoiceInterface) {
            throw new UnexpectedTypeException($invoice, Model\OrderInvoiceInterface::class);
        }

        return $this->invoices->contains($invoice);
    }

    public function addInvoice(Invoice\InvoiceInterface $invoice): Invoice\InvoiceSubjectInterface
    {
        if (!$invoice instanceof Model\OrderInvoiceInterface) {
            throw new UnexpectedTypeException($invoice, Model\OrderInvoiceInterface::class);
        }

        if (!$this->hasInvoice($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setOrder($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice\InvoiceInterface $invoice): Invoice\InvoiceSubjectInterface
    {
        if (!$invoice instanceof Model\OrderInvoiceInterface) {
            throw new UnexpectedTypeException($invoice, Model\OrderInvoiceInterface::class);
        }

        if ($this->hasInvoice($invoice)) {
            $this->invoices->removeElement($invoice);
            $invoice->setOrder(null);
        }

        return $this;
    }

    public function isFullyInvoiced(): bool
    {
        return $this->invoiceTotal >= $this->grandTotal;
    }

    public function getCompletedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?DateTimeInterface $completedAt): Model\OrderInterface
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getRevenueTotal(): ?Decimal
    {
        return $this->revenueTotal;
    }

    public function setRevenueTotal(?Decimal $amount): Model\OrderInterface
    {
        $this->revenueTotal = $amount;

        return $this;
    }

    public function getMarginTotal(): ?Decimal
    {
        return $this->marginTotal;
    }

    public function setMarginTotal(?Decimal $amount): Model\OrderInterface
    {
        $this->marginTotal = $amount;

        return $this;
    }

    public function getItemsCount(): int
    {
        return $this->itemsCount;
    }

    public function setItemsCount(int $count): Model\OrderInterface
    {
        $this->itemsCount = $count;

        return $this;
    }

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
            if ($shipment->isReturn() && Shipment\ShipmentStates::isStockableState($shipment, false)) {
                return true;
            }
        }

        return false;
    }
}

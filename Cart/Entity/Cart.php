<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Entity;

use DateTimeInterface;
use Ekyna\Component\Commerce\Cart\Model;
use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Payment\Model as Payment;

/**
 * Class Cart
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Cart extends AbstractSale implements Model\CartInterface
{
    protected ?DateTimeInterface $expiresAt = null;


    public function __construct()
    {
        parent::__construct();

        $this->state = Model\CartStates::STATE_NEW;
    }

    /**
     * @return Model\CartAddressInterface|null
     */
    public function getInvoiceAddress(): ?Common\SaleAddressInterface
    {
        return $this->invoiceAddress;
    }

    public function setInvoiceAddress(?Common\SaleAddressInterface $address): Common\SaleInterface
    {
        if ($address && !$address instanceof Model\CartAddressInterface) {
            throw new UnexpectedTypeException($address, Model\CartAddressInterface::class);
        }

        if ($address === $this->invoiceAddress) {
            return $this;
        }

        if ($previous = $this->invoiceAddress) {
            $this->invoiceAddress = null;
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $previous->setInvoiceCart(null);
        }

        if ($this->invoiceAddress = $address) {
            $address->setInvoiceCart($this);
        }

        return $this;
    }

    /**
     * @return Model\CartAddressInterface|null
     */
    public function getDeliveryAddress(): ?Common\SaleAddressInterface
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Common\SaleAddressInterface $address): Common\SaleInterface
    {
        if ($address && !$address instanceof Model\CartAddressInterface) {
            throw new UnexpectedTypeException($address, Model\CartAddressInterface::class);
        }

        if ($address === $this->deliveryAddress) {
            return $this;
        }

        if ($previous = $this->deliveryAddress) {
            $this->deliveryAddress = null;
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $previous->setDeliveryCart(null);
        }

        if ($this->deliveryAddress = $address) {
            $address->setDeliveryCart($this);
        }

        return $this;
    }

    public function getDestinationAddress(): ?Common\SaleAddressInterface
    {
        return null;
    }

    public function setDestinationAddress(?Common\SaleAddressInterface $address): Common\SaleInterface
    {
        throw new RuntimeException('Final address is not supported for carts');
    }

    public function hasAttachment(Common\SaleAttachmentInterface $attachment): bool
    {
        if (!$attachment instanceof Model\CartAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\CartAttachmentInterface::class);
        }

        return $this->attachments->contains($attachment);
    }

    public function addAttachment(Common\SaleAttachmentInterface $attachment): Common\SaleInterface
    {
        if (!$attachment instanceof Model\CartAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\CartAttachmentInterface::class);
        }

        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setCart($this);
        }

        return $this;
    }

    public function removeAttachment(Common\SaleAttachmentInterface $attachment): Common\SaleInterface
    {
        if (!$attachment instanceof Model\CartAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\CartAttachmentInterface::class);
        }

        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setCart(null);
        }

        return $this;
    }

    public function hasItem(Common\SaleItemInterface $item): bool
    {
        if (!$item instanceof Model\CartItemInterface) {
            throw new UnexpectedTypeException($item, Model\CartItemInterface::class);
        }

        return $this->items->contains($item);
    }

    public function addItem(Common\SaleItemInterface $item): Common\SaleInterface
    {
        if (!$item instanceof Model\CartItemInterface) {
            throw new UnexpectedTypeException($item, Model\CartItemInterface::class);
        }

        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setCart($this);
        }

        return $this;
    }

    public function removeItem(Common\SaleItemInterface $item): Common\SaleInterface
    {
        if (!$item instanceof Model\CartItemInterface) {
            throw new UnexpectedTypeException($item, Model\CartItemInterface::class);
        }

        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setCart(null);
        }

        return $this;
    }

    public function hasAdjustment(Common\AdjustmentInterface $adjustment): bool
    {
        if (!$adjustment instanceof Model\CartAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\CartAdjustmentInterface::class);
        }

        return $this->adjustments->contains($adjustment);
    }

    public function addAdjustment(Common\AdjustmentInterface $adjustment): Common\AdjustableInterface
    {
        if (!$adjustment instanceof Model\CartAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\CartAdjustmentInterface::class);
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setCart($this);
        }

        return $this;
    }

    public function removeAdjustment(Common\AdjustmentInterface $adjustment): Common\AdjustableInterface
    {
        if (!$adjustment instanceof Model\CartAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\CartAdjustmentInterface::class);
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setCart(null);
        }

        return $this;
    }

    public function hasNotification(Common\NotificationInterface $notification): bool
    {
        if (!$notification instanceof Model\CartNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\CartNotificationInterface::class);
        }

        return $this->notifications->contains($notification);
    }

    public function addNotification(Common\NotificationInterface $notification): Common\NotifiableInterface
    {
        if (!$notification instanceof Model\CartNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\CartNotificationInterface::class);
        }

        if (!$this->hasNotification($notification)) {
            $this->notifications->add($notification);
            $notification->setCart($this);
        }

        return $this;
    }

    public function removeNotification(Common\NotificationInterface $notification): Common\NotifiableInterface
    {
        if (!$notification instanceof Model\CartNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\CartNotificationInterface::class);
        }

        if ($this->hasNotification($notification)) {
            $this->notifications->removeElement($notification);
            $notification->setCart(null);
        }

        return $this;
    }

    public function hasPayment(Payment\PaymentInterface $payment): bool
    {
        if (!$payment instanceof Model\CartPaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\CartPaymentInterface::class);
        }

        return $this->payments->contains($payment);
    }

    public function addPayment(Payment\PaymentInterface $payment): Payment\PaymentSubjectInterface
    {
        if (!$payment instanceof Model\CartPaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\CartPaymentInterface::class);
        }

        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setCart($this);
        }

        return $this;
    }

    public function removePayment(Payment\PaymentInterface $payment): Payment\PaymentSubjectInterface
    {
        if (!$payment instanceof Model\CartPaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\CartPaymentInterface::class);
        }

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setCart(null);
        }

        return $this;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt): Model\CartInterface
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}

<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Model;
use Ekyna\Component\Commerce\Payment\Model as Payment;

/**
 * Class Cart
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Cart extends AbstractSale implements Model\CartInterface
{
    /**
     * @var \DateTime
     */
    protected $expiresAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->state = Model\CartStates::STATE_NEW;
        $this->source = Common\SaleSources::SOURCE_WEB;
    }

    /**
     * @inheritdoc
     *
     * @return Model\CartAddressInterface
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
        if ($address && !$address instanceof Model\CartAddressInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartAddressInterface::class);
        }

        if ($address !== $current = $this->getInvoiceAddress()) {
            if (null !== $current) {
                $current->setInvoiceCart(null);
            }

            $this->invoiceAddress = $address;

            if (null !== $address) {
                $address->setInvoiceCart($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Model\CartAddressInterface
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
        if ($address && !$address instanceof Model\CartAddressInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartAddressInterface::class);
        }

        if ($address !== $current = $this->getDeliveryAddress()) {
            if (null !== $current) {
                $current->setDeliveryCart(null);
            }

            $this->deliveryAddress = $address;

            if (null !== $address) {
                $address->setDeliveryCart($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\CartAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartAttachmentInterface::class);
        }

        return $this->attachments->contains($attachment);
    }

    /**
     * @inheritdoc
     */
    public function addAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\CartAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartAttachmentInterface::class);
        }

        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setCart($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\CartAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartAttachmentInterface::class);
        }

        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setCart(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartItemInterface::class);
        }

        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartItemInterface::class);
        }

        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setCart($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartItemInterface::class);
        }

        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setCart(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartAdjustmentInterface::class);
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartAdjustmentInterface::class);
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setCart($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartAdjustmentInterface::class);
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setCart(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasNotification(Common\NotificationInterface $notification)
    {
        if (!$notification instanceof Model\CartNotificationInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartNotificationInterface::class);
        }

        return $this->notifications->contains($notification);
    }

    /**
     * @inheritdoc
     */
    public function addNotification(Common\NotificationInterface $notification)
    {
        if (!$notification instanceof Model\CartNotificationInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartNotificationInterface::class);
        }

        if (!$this->hasNotification($notification)) {
            $this->notifications->add($notification);
            $notification->setCart($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeNotification(Common\NotificationInterface $notification)
    {
        if (!$notification instanceof Model\CartNotificationInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartNotificationInterface::class);
        }

        if ($this->hasNotification($notification)) {
            $this->notifications->removeElement($notification);
            $notification->setCart(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasPayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\CartPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartPaymentInterface::class);
        }

        return $this->payments->contains($payment);
    }

    /**
     * @inheritdoc
     */
    public function addPayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\CartPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartPaymentInterface::class);
        }

        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setCart($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\CartPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\CartPaymentInterface::class);
        }

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setCart(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @inheritdoc
     */
    public function setExpiresAt(\DateTime $expiresAt = null)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}

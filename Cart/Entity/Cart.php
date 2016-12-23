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
        $this->state = Model\CartStates::STATE_NEW;

        parent::__construct();
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
    public function setInvoiceAddress(Common\AddressInterface $address)
    {
        if (!$address instanceof Model\CartAddressInterface) {
            throw new InvalidArgumentException('Expected instance of CartAddressInterface.');
        }

        if ($address != $this->invoiceAddress) {
            $this->invoiceAddress = $address;
            $address->setInvoiceCart($this);
        }

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
    public function setDeliveryAddress(Common\AddressInterface $address = null)
    {
        if (null !== $address && !$address instanceof Model\CartAddressInterface) {
            throw new InvalidArgumentException('Expected instance of CartAddressInterface.');
        }

        if ($address != $this->deliveryAddress) {
            $this->deliveryAddress = $address;
            $address->setDeliveryCart($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\CartAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAttachmentInterface.");
        }

        return $this->attachments->contains($attachment);
    }

    /**
     * @inheritdoc
     */
    public function addAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\CartAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAttachmentInterface.");
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
            throw new InvalidArgumentException("Expected instance of CartAttachmentInterface.");
        }

        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            //$attachment->setCart(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
        }

        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
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
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
        }

        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            //$item->setCart(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAdjustmentInterface.");
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAdjustmentInterface.");
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
            throw new InvalidArgumentException("Expected instance of CartAdjustmentInterface.");
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            //$adjustment->setCart(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasPayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\CartPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of CartPaymentInterface.");
        }

        return $this->payments->contains($payment);
    }

    /**
     * @inheritdoc
     */
    public function addPayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\CartPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of CartPaymentInterface.");
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
            throw new InvalidArgumentException("Expected instance of CartPaymentInterface.");
        }

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            //$payment->setCart(null);
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

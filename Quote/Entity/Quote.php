<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model;
use Ekyna\Component\Commerce\Payment\Model as Payment;

/**
 * Class Quote
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Quote extends AbstractSale implements Model\QuoteInterface
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

        $this->state = Model\QuoteStates::STATE_NEW;
    }

    /**
     * @inheritdoc
     *
     * @return Model\QuoteAddressInterface
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
        if (null !== $address && !$address instanceof Model\QuoteAddressInterface) {
            throw new InvalidArgumentException('Expected instance of QuoteAddressInterface.');
        }

        if ($address !== $current = $this->getInvoiceAddress()) {
            if (null !== $current) {
                $current->setInvoiceQuote(null);
            }

            $this->invoiceAddress = $address;

            if (null !== $address) {
                $address->setInvoiceQuote($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Model\QuoteAddressInterface
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
        if (null !== $address && !$address instanceof Model\QuoteAddressInterface) {
            throw new InvalidArgumentException('Expected instance of QuoteAddressInterface.');
        }

        if ($address !== $current = $this->getDeliveryAddress()) {
            if (null !== $current) {
                $current->setDeliveryQuote(null);
            }

            $this->deliveryAddress = $address;

            if (null !== $address) {
                $address->setDeliveryQuote($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\QuoteAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteAttachmentInterface.");
        }

        return $this->attachments->contains($attachment);
    }

    /**
     * @inheritdoc
     */
    public function addAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\QuoteAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteAttachmentInterface.");
        }

        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setQuote($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\QuoteAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteAttachmentInterface.");
        }

        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setQuote(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemInterface.");
        }

        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemInterface.");
        }

        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setQuote($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemInterface.");
        }

        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setQuote(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\QuoteAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteAdjustmentInterface.");
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\QuoteAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteAdjustmentInterface.");
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setQuote($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\QuoteAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteAdjustmentInterface.");
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setQuote(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasPayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\QuotePaymentInterface) {
            throw new InvalidArgumentException("Expected instance of QuotePaymentInterface.");
        }

        return $this->payments->contains($payment);
    }

    /**
     * @inheritdoc
     */
    public function addPayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\QuotePaymentInterface) {
            throw new InvalidArgumentException("Expected instance of QuotePaymentInterface.");
        }

        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setQuote($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\QuotePaymentInterface) {
            throw new InvalidArgumentException("Expected instance of QuotePaymentInterface.");
        }

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setQuote(null);
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

    /**
     * @inheritdoc
     */
    public function isExpired()
    {
        if (null === $this->expiresAt) {
            return false;
        }

        $diff = $this->expiresAt->diff((new \DateTime())->setTime(0,0,0));

        return 0 < $diff->days && !$diff->invert;
    }

    /**
     * @inheritdoc
     */
    public function hasVoucher()
    {
        return !empty($this->voucherNumber) && null !== $this->getVoucherAttachment();
    }

    /**
     * @inheritdoc
     */
    public function getVoucherAttachment()
    {
        foreach ($this->attachments as $attachment) {
            if ($attachment->getType() === DocumentTypes::TYPE_VOUCHER) {
                return $attachment;
            }
        }

        return null;
    }
}

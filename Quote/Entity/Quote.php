<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class Quote
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Quote extends AbstractSale implements Model\QuoteInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $paymentState;

    /**
     * @var float
     */
    protected $paidTotal;

    /**
     * @var ArrayCollection|PaymentInterface[]
     */
    protected $payments;

    /**
     * @var \DateTime
     */
    protected $completedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->state = Model\QuoteStates::STATE_NEW;
        $this->paymentState = PaymentStates::STATE_NEW;

        $this->paidTotal = 0;

        $this->payments = new ArrayCollection();
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
    public function setInvoiceAddress(Common\AddressInterface $address)
    {
        if (!$address instanceof Model\QuoteAddressInterface) {
            throw new InvalidArgumentException('Unexpected address type.');
        }

        $this->invoiceAddress = $address;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryAddress(Common\AddressInterface $address = null)
    {
        if (null !== $address && !($address instanceof Model\QuoteAddressInterface)) {
            throw new InvalidArgumentException('Unexpected address type.');
        }

        // TODO remove from database if null ?
        $this->deliveryAddress = $address;

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
            $item->setQuote($this);
            $this->items->add($item);
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
            $item->setQuote(null);
            $this->items->removeElement($item);
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
            $adjustment->setQuote($this);
            $this->adjustments->add($adjustment);
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
            $adjustment->setQuote(null);
            $this->adjustments->removeElement($adjustment);
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
    public function hasPayment(Model\QuotePaymentInterface $payment)
    {
        return $this->payments->contains($payment);
    }

    /**
     * @inheritdoc
     */
    public function addPayment(Model\QuotePaymentInterface $payment)
    {
        if (!$this->hasPayment($payment)) {
            $payment->setQuote($this);
            $this->payments->add($payment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePayment(Model\QuotePaymentInterface $payment)
    {
        if ($this->hasPayment($payment)) {
            $payment->setQuote(null);
            $this->payments->removeElement($payment);
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
}

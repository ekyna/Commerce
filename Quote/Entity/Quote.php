<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
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
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Model\QuoteStates::STATE_NEW;

        parent::__construct();
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
            $payment->setQuote($this);
            $this->payments->add($payment);
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
            $payment->setQuote(null);
            $this->payments->removeElement($payment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function validateAddressClass(Common\AddressInterface $address)
    {
        if (!$address instanceof Model\QuoteAddressInterface) {
            throw new InvalidArgumentException('Expected instance of QuoteAddressInterface.');
        }
    }
}

<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class InvoicePayment
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePayment
{
    /**
     * @var PaymentInterface
     */
    private $payment;

    /**
     * @var InvoiceInterface
     */
    private $invoice;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $realAmount;


    /**
     * Returns the payment.
     *
     * @return PaymentInterface|null
     */
    public function getPayment(): ?PaymentInterface
    {
        return $this->payment;
    }

    /**
     * Sets the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return InvoicePayment
     */
    public function setPayment(PaymentInterface $payment): InvoicePayment
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Returns the invoice.
     *
     * @return InvoiceInterface|null
     */
    public function getInvoice(): ?InvoiceInterface
    {
        return $this->invoice;
    }

    /**
     * Sets the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return InvoicePayment
     */
    public function setInvoice(InvoiceInterface $invoice): InvoicePayment
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Sets the amount.
     *
     * @param float $amount
     *
     * @return InvoicePayment
     */
    public function setAmount(float $amount): InvoicePayment
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Returns the realAmount.
     *
     * @return float
     */
    public function getRealAmount(): float
    {
        return $this->realAmount;
    }

    /**
     * Sets the realAmount.
     *
     * @param float $amount
     *
     * @return InvoicePayment
     */
    public function setRealAmount(float $amount): InvoicePayment
    {
        $this->realAmount = $amount;

        return $this;
    }
}

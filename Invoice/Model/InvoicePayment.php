<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class InvoicePayment
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePayment
{
    private ?PaymentInterface $payment = null;
    private ?InvoiceInterface $invoice = null;
    private Decimal           $amount;
    private Decimal           $realAmount;

    public function __construct()
    {
        $this->amount = new Decimal(0);
        $this->realAmount = new Decimal(0);
    }

    public function getPayment(): ?PaymentInterface
    {
        return $this->payment;
    }

    public function setPayment(PaymentInterface $payment): InvoicePayment
    {
        $this->payment = $payment;

        return $this;
    }

    public function getInvoice(): ?InvoiceInterface
    {
        return $this->invoice;
    }

    public function setInvoice(InvoiceInterface $invoice): InvoicePayment
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function setAmount(Decimal $amount): InvoicePayment
    {
        $this->amount = $amount;

        return $this;
    }

    public function getRealAmount(): Decimal
    {
        return $this->realAmount;
    }

    public function setRealAmount(Decimal $amount): InvoicePayment
    {
        $this->realAmount = $amount;

        return $this;
    }
}

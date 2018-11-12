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
     * @var float
     */
    private $amount;


    /**
     * Returns the payment.
     *
     * @return PaymentInterface
     */
    public function getPayment()
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
    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount()
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
    public function setAmount(float $amount)
    {
        $this->amount = $amount;

        return $this;
    }
}

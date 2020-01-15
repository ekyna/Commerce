<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectTrait;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;

/**
 * Trait PaymentSubjectTrait
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait PaymentSubjectTrait
{
    use ExchangeSubjectTrait;

    /**
     * @var float
     */
    protected $depositTotal;

    /**
     * @var float
     */
    protected $grandTotal;

    /**
     * @var float
     */
    protected $paidTotal;

    /**
     * @var float
     */
    protected $refundedTotal;

    /**
     * @var float
     */
    protected $pendingTotal;

    /**
     * @var float
     */
    protected $outstandingAccepted;

    /**
     * @var float
     */
    protected $outstandingExpired;

    /**
     * @var float
     */
    protected $outstandingLimit;

    /**
     * @var \DateTime
     */
    protected $outstandingDate;

    /**
     * @var string
     */
    protected $paymentState;

    /**
     * @var PaymentMethodInterface|null
     */
    protected $paymentMethod;

    /**
     * @var \Doctrine\Common\Collections\Collection|PaymentInterface[]
     */
    protected $payments;


    /**
     * Initializes the payment subject.
     */
    protected function initializePaymentSubject()
    {
        $this->depositTotal = 0;
        $this->grandTotal = 0;
        $this->paidTotal = 0;
        $this->refundedTotal = 0;
        $this->pendingTotal = 0;
        $this->outstandingAccepted = 0;
        $this->outstandingExpired = 0;
        $this->outstandingLimit = 0;

        $this->paymentState = PaymentStates::STATE_NEW;
        $this->payments = new ArrayCollection();
    }

    /**
     * Returns the deposit total.
     *
     * @return float
     */
    public function getDepositTotal(): float
    {
        return $this->depositTotal;
    }

    /**
     * Sets the deposit total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setDepositTotal(float $total): PaymentSubjectInterface
    {
        $this->depositTotal = $total;

        return $this;
    }

    /**
     * Returns the grand total.
     *
     * @return float
     */
    public function getGrandTotal(): float
    {
        return $this->grandTotal;
    }

    /**
     * Sets the grand total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setGrandTotal(float $total): PaymentSubjectInterface
    {
        $this->grandTotal = $total;

        return $this;
    }

    /**
     * Returns the paid total.
     *
     * @return float
     */
    public function getPaidTotal(): float
    {
        return $this->paidTotal;
    }

    /**
     * Sets the paid total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaidTotal(float $total): PaymentSubjectInterface
    {
        $this->paidTotal = $total;

        return $this;
    }

    /**
     * Returns the refunded total.
     *
     * @return float
     */
    public function getRefundedTotal(): float
    {
        return $this->refundedTotal;
    }

    /**
     * Sets the refunded total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setRefundedTotal(float $total): PaymentSubjectInterface
    {
        $this->refundedTotal = $total;

        return $this;
    }

    /**
     * Returns the pending total.
     *
     * @return float
     */
    public function getPendingTotal(): float
    {
        return $this->pendingTotal;
    }

    /**
     * Sets the pending total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPendingTotal(float $total): PaymentSubjectInterface
    {
        $this->pendingTotal = $total;

        return $this;
    }

    /**
     * Returns the accepted outstanding total.
     *
     * @return float
     */
    public function getOutstandingAccepted(): float
    {
        return $this->outstandingAccepted;
    }

    /**
     * Sets the accepted outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingAccepted(float $total): PaymentSubjectInterface
    {
        $this->outstandingAccepted = $total;

        return $this;
    }

    /**
     * Returns the expired outstanding total.
     *
     * @return float
     */
    public function getOutstandingExpired(): float
    {
        return $this->outstandingExpired;
    }

    /**
     * Sets the expired outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingExpired(float $total): PaymentSubjectInterface
    {
        $this->outstandingExpired = $total;

        return $this;
    }

    /**
     * Returns the outstanding limit.
     *
     * @return float
     */
    public function getOutstandingLimit(): float
    {
        return $this->outstandingLimit;
    }

    /**
     * Sets the outstanding amount.
     *
     * @param float $amount
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingLimit(float $amount): PaymentSubjectInterface
    {
        $this->outstandingLimit = (float)$amount;

        return $this;
    }

    /**
     * Returns the outstanding date.
     *
     * @return \DateTime|null
     */
    public function getOutstandingDate(): ?\DateTime
    {
        return $this->outstandingDate;
    }

    /**
     * Sets the outstanding date.
     *
     * @param \DateTime $date
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingDate(\DateTime $date = null): PaymentSubjectInterface
    {
        $this->outstandingDate = $date;

        return $this;
    }

    /**
     * Returns the (default) payment method.
     *
     * @return PaymentMethodInterface|null
     */
    public function getPaymentMethod(): ?PaymentMethodInterface
    {
        return $this->paymentMethod;
    }

    /**
     * Sets the (default) payment method.
     *
     * @param PaymentMethodInterface|null $method
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaymentMethod(PaymentMethodInterface $method = null): PaymentSubjectInterface
    {
        $this->paymentMethod = $method;

        return $this;
    }

    /**
     * Returns the payment state.
     *
     * @return string
     */
    public function getPaymentState(): string
    {
        return $this->paymentState;
    }

    /**
     * Sets the payment state.
     *
     * @param string $state
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaymentState(string $state): PaymentSubjectInterface
    {
        $this->paymentState = $state;

        return $this;
    }

    /**
     * Returns whether or not the subject has at least one payment or refund (with any state).
     *
     * @return bool
     */
    public function hasPayments(): bool
    {
        return 0 < $this->payments->count();
    }

    /**
     * Returns whether the subject has at least one paid (or refunded) payment.
     *
     * @param bool $orRefunded
     *
     * @return bool
     */
    public function hasPaidPayments(bool $orRefunded = false): bool
    {
        foreach ($this->payments as $payment) {
            if (PaymentStates::isPaidState($payment, $orRefunded)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the payments.
     *
     * @param bool $filter TRUE for payments, FALSE for refunds, NULL for all
     *
     * @return Collection|PaymentInterface[]
     */
    public function getPayments(bool $filter = null): Collection
    {
        if (is_null($filter)) {
            return $this->payments;
        }

        return $this->payments->filter(function(PaymentInterface $payment) use ($filter) {
            return $filter xor $payment->isRefund();
        });
    }

    /**
     * Returns whether or not the subject is fully paid.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        if ($this instanceof InvoiceSubjectInterface && $this->hasInvoices()) {
            $total = $this->getInvoiceTotal() - $this->getCreditTotal();
        } else {
            $total = $this->grandTotal;
        }

        $paid = $this->paidTotal - $this->refundedTotal;

        // TRUE If paid total is greater than or equals grand total
        return 0 <= Money::compare($paid, $total, $this->getCurrency()->getCode());
    }
}

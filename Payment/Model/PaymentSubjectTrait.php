<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectTrait;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;

/**
 * Trait PaymentSubjectTrait
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait PaymentSubjectTrait
{
    use ExchangeSubjectTrait;

    protected Decimal $depositTotal;
    protected Decimal $grandTotal;
    protected Decimal $paidTotal;
    protected Decimal $refundedTotal;
    protected Decimal $pendingTotal;
    protected Decimal $outstandingAccepted;
    protected Decimal $outstandingExpired;
    protected Decimal $outstandingLimit;
    protected ?DateTimeInterface $outstandingDate = null;
    protected string $paymentState;
    protected ?PaymentMethodInterface $paymentMethod = null;
    /** @var Collection<PaymentInterface> */
    protected Collection $payments;


    /**
     * Initializes the payment subject.
     */
    protected function initializePaymentSubject(): void
    {
        $this->depositTotal = new Decimal(0);
        $this->grandTotal = new Decimal(0);
        $this->paidTotal = new Decimal(0);
        $this->refundedTotal = new Decimal(0);
        $this->pendingTotal = new Decimal(0);
        $this->outstandingAccepted = new Decimal(0);
        $this->outstandingExpired = new Decimal(0);
        $this->outstandingLimit = new Decimal(0);

        $this->paymentState = PaymentStates::STATE_NEW;
        $this->payments = new ArrayCollection();
    }

    public function getDepositTotal(): Decimal
    {
        return $this->depositTotal;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setDepositTotal(Decimal $total): PaymentSubjectInterface
    {
        $this->depositTotal = $total;

        return $this;
    }

    public function getGrandTotal(): Decimal
    {
        return $this->grandTotal;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setGrandTotal(Decimal $total): PaymentSubjectInterface
    {
        $this->grandTotal = $total;

        return $this;
    }

    public function getPaidTotal(): Decimal
    {
        return $this->paidTotal;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setPaidTotal(Decimal $total): PaymentSubjectInterface
    {
        $this->paidTotal = $total;

        return $this;
    }

    public function getRefundedTotal(): Decimal
    {
        return $this->refundedTotal;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setRefundedTotal(Decimal $total): PaymentSubjectInterface
    {
        $this->refundedTotal = $total;

        return $this;
    }

    public function getPendingTotal(): Decimal
    {
        return $this->pendingTotal;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setPendingTotal(Decimal $total): PaymentSubjectInterface
    {
        $this->pendingTotal = $total;

        return $this;
    }

    public function getOutstandingAccepted(): Decimal
    {
        return $this->outstandingAccepted;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingAccepted(Decimal $total): PaymentSubjectInterface
    {
        $this->outstandingAccepted = $total;

        return $this;
    }

    /**
     * Returns the expired outstanding total.
     */
    public function getOutstandingExpired(): Decimal
    {
        return $this->outstandingExpired;
    }

    /**
     * Sets the expired outstanding total.
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingExpired(Decimal $total): PaymentSubjectInterface
    {
        $this->outstandingExpired = $total;

        return $this;
    }

    public function getOutstandingLimit(): Decimal
    {
        return $this->outstandingLimit;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingLimit(Decimal $amount): PaymentSubjectInterface
    {
        $this->outstandingLimit = $amount;

        return $this;
    }

    public function getOutstandingDate(): ?DateTimeInterface
    {
        return $this->outstandingDate;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingDate(?DateTimeInterface $date): PaymentSubjectInterface
    {
        $this->outstandingDate = $date;

        return $this;
    }

    /**
     * Returns the (default) payment method.
     */
    public function getPaymentMethod(): ?PaymentMethodInterface
    {
        return $this->paymentMethod;
    }

    /**
     * Sets the (default) payment method.
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaymentMethod(?PaymentMethodInterface $method): PaymentSubjectInterface
    {
        $this->paymentMethod = $method;

        return $this;
    }

    public function getPaymentState(): string
    {
        return $this->paymentState;
    }

    /**
     * @return $this|PaymentSubjectInterface
     */
    public function setPaymentState(string $state): PaymentSubjectInterface
    {
        $this->paymentState = $state;

        return $this;
    }

    /**
     * Returns whether or not the subject has at least one payment or refund (with any state).
     */
    public function hasPayments(): bool
    {
        return 0 < $this->payments->count();
    }

    /**
     * Returns whether the subject has at least one paid (or refunded) payment.
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
     * @return Collection<PaymentInterface>
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
     * Returns whether the subject is fully paid.
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
        return $total <= $paid;
    }
}

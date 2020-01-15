<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;

/**
 * Interface PaymentSubjectInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentSubjectInterface extends ExchangeSubjectInterface
{
    /**
     * Returns the deposit total.
     *
     * @return float
     */
    public function getDepositTotal(): float;

    /**
     * Sets the deposit total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setDepositTotal(float $total): PaymentSubjectInterface;

    /**
     * Returns the grand total.
     *
     * @return float
     */
    public function getGrandTotal(): float;

    /**
     * Sets the grand total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setGrandTotal(float $total): PaymentSubjectInterface;

    /**
     * Returns the paid total.
     *
     * @return float
     */
    public function getPaidTotal(): float;

    /**
     * Sets the paid total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaidTotal(float $total): PaymentSubjectInterface;

    /**
     * Returns the refunded total.
     *
     * @return float
     */
    public function getRefundedTotal(): float;

    /**
     * Sets the refunded total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setRefundedTotal(float $total): PaymentSubjectInterface;

    /**
     * Returns the pending total.
     *
     * @return float
     */
    public function getPendingTotal(): float;

    /**
     * Sets the pending total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPendingTotal(float $total): PaymentSubjectInterface;

    /**
     * Returns the accepted outstanding total.
     *
     * @return float
     */
    public function getOutstandingAccepted(): float;

    /**
     * Sets the accepted outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingAccepted(float $total): PaymentSubjectInterface;

    /**
     * Returns the expired outstanding total.
     *
     * @return float
     */
    public function getOutstandingExpired(): float;

    /**
     * Sets the expired outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingExpired(float $total): PaymentSubjectInterface;

    /**
     * Returns the outstanding limit.
     *
     * @return float
     */
    public function getOutstandingLimit(): float;

    /**
     * Sets the outstanding amount.
     *
     * @param float $amount
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingLimit(float $amount): PaymentSubjectInterface;

    /**
     * Returns the outstanding date.
     *
     * @return \DateTime|null
     */
    public function getOutstandingDate(): ?\DateTime;

    /**
     * Sets the outstanding date.
     *
     * @param \DateTime $date
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingDate(\DateTime $date = null): PaymentSubjectInterface;

    /**
     * Returns the (default) payment method.
     *
     * @return PaymentMethodInterface|null
     */
    public function getPaymentMethod(): ?PaymentMethodInterface;

    /**
     * Sets the (default) payment method.
     *
     * @param PaymentMethodInterface|null $method
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaymentMethod(PaymentMethodInterface $method = null): PaymentSubjectInterface;

    /**
     * Returns the payment state.
     *
     * @return string
     */
    public function getPaymentState(): string;

    /**
     * Sets the payment state.
     *
     * @param string $state
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaymentState(string $state): PaymentSubjectInterface;

    /**
     * Returns whether the subject has at least one payment or refund (with any state).
     *
     * @return bool
     */
    public function hasPayments(): bool;

    /**
     * Returns whether the subject has at least one paid (or refunded) payment.
     *
     * @param bool $orRefunded
     *
     * @return bool
     */
    public function hasPaidPayments(bool $orRefunded = false): bool;

    /**
     * Returns whether the order has the payment or not.
     *
     * @param PaymentInterface $payment
     *
     * @return bool
     */
    public function hasPayment(PaymentInterface $payment): bool;

    /**
     * Adds the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return $this|PaymentSubjectInterface
     */
    public function addPayment(PaymentInterface $payment): PaymentSubjectInterface;

    /**
     * Removes the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return $this|PaymentSubjectInterface
     */
    public function removePayment(PaymentInterface $payment): PaymentSubjectInterface;

    /**
     * Returns the payments.
     *
     * @param bool|null $filter TRUE for payments, FALSE for refunds, NULL for all
     *
     * @return Collection|PaymentInterface[]
     */
    public function getPayments(bool $filter = null): Collection;

    /**
     * Returns whether or not the subject is fully paid.
     *
     * @return bool
     */
    public function isPaid(): bool;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;

/**
 * Interface PaymentSubjectInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentSubjectInterface extends ExchangeSubjectInterface
{
    public function getDepositTotal(): Decimal;

    public function setDepositTotal(Decimal $total): PaymentSubjectInterface;

    public function getGrandTotal(): Decimal;

    public function setGrandTotal(Decimal $total): PaymentSubjectInterface;

    public function getPaidTotal(): Decimal;

    public function setPaidTotal(Decimal $total): PaymentSubjectInterface;

    public function getRefundedTotal(): Decimal;

    public function setRefundedTotal(Decimal $total): PaymentSubjectInterface;

    public function getPendingTotal(): Decimal;

    public function setPendingTotal(Decimal $total): PaymentSubjectInterface;

    /**
     * Returns the accepted outstanding total.
     */
    public function getOutstandingAccepted(): Decimal;

    /**
     * Sets the accepted outstanding total.
     */
    public function setOutstandingAccepted(Decimal $total): PaymentSubjectInterface;

    /**
     * Returns the expired outstanding total.
     */
    public function getOutstandingExpired(): Decimal;

    /**
     * Sets the expired outstanding total.
     */
    public function setOutstandingExpired(Decimal $total): PaymentSubjectInterface;

    public function getOutstandingLimit(): Decimal;

    public function setOutstandingLimit(Decimal $amount): PaymentSubjectInterface;

    public function getOutstandingDate(): ?DateTimeInterface;

    public function setOutstandingDate(?DateTimeInterface $date): PaymentSubjectInterface;

    /**
     * Returns the (default) payment method.
     */
    public function getPaymentMethod(): ?PaymentMethodInterface;

    /**
     * Sets the (default) payment method.
     */
    public function setPaymentMethod(?PaymentMethodInterface $method): PaymentSubjectInterface;

    public function getPaymentState(): string;

    public function setPaymentState(string $state): PaymentSubjectInterface;

    /**
     * Returns whether the subject has at least one payment or refund (with any state).
     */
    public function hasPayments(): bool;

    /**
     * Returns whether the subject has at least one paid (or refunded) payment.
     */
    public function hasPaidPayments(bool $orRefunded = false): bool;

    /**
     * Returns whether the order has the payment or not.
     */
    public function hasPayment(PaymentInterface $payment): bool;

    public function addPayment(PaymentInterface $payment): PaymentSubjectInterface;

    public function removePayment(PaymentInterface $payment): PaymentSubjectInterface;

    /**
     * Returns the payments.
     *
     * @param bool|null $filter TRUE for payments, FALSE for refunds, NULL for all
     *
     * @return Collection<int, PaymentInterface>
     */
    public function getPayments(bool $filter = null): Collection;

    /**
     * Returns whether the subject is fully paid.
     */
    public function isPaid(): bool;
}

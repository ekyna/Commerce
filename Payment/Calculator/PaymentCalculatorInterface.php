<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;

/**
 * Interface PaymentCalculatorInterface
 * @package Ekyna\Component\Commerce\Payment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentCalculatorInterface
{
    /**
     * Returns the subject's payment amounts.
     *
     * @return array<Decimal, Decimal, Decimal, Decimal, Decimal> (total, paid, refunded, deposit, pending)
     */
    public function getPaymentAmounts(PaymentSubjectInterface $subject, string $currency = null): array;

    /**
     * Calculates the paid total.
     */
    public function calculatePaidTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal;

    /**
     * Calculates the refunded total.
     */
    public function calculateRefundedTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal;

    /**
     * Calculates the accepted outstanding total.
     */
    public function calculateOutstandingAcceptedTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal;

    /**
     * Calculates the expired outstanding total.
     */
    public function calculateOutstandingExpiredTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal;

    /**
     * Calculates the failed total.
     */
    public function calculateFailedTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal;

    /**
     * Calculates the canceled total.
     */
    public function calculateCanceledTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal;

    /**
     * Calculates the offline pending total.
     */
    public function calculateOfflinePendingTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal;

    /**
     * Calculates the subject's expected payment amount.
     */
    public function calculateExpectedPaymentAmount(PaymentSubjectInterface $subject, string $currency = null): Decimal;

    /**
     * Calculates the subject's expected refund amount.
     */
    public function calculateExpectedRefundAmount(PaymentSubjectInterface $subject, string $currency = null): Decimal;
}

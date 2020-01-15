<?php

namespace Ekyna\Component\Commerce\Payment\Calculator;

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
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return array [total, paid, refunded, deposit, pending]
     */
    public function getPaymentAmounts(PaymentSubjectInterface $subject, string $currency = null): array;

    /**
     * Calculates the paid total.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculatePaidTotal(PaymentSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the refunded total.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculateRefundedTotal(PaymentSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the accepted outstanding total.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculateOutstandingAcceptedTotal(PaymentSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the expired outstanding total.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculateOutstandingExpiredTotal(PaymentSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the failed total.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculateFailedTotal(PaymentSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the canceled total.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculateCanceledTotal(PaymentSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the offline pending total.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculateOfflinePendingTotal(PaymentSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the subject's expected payment amount.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculateExpectedPaymentAmount(PaymentSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the subject's expected refund amount.
     *
     * @param PaymentSubjectInterface $subject
     * @param string|null             $currency
     *
     * @return float
     */
    public function calculateExpectedRefundAmount(PaymentSubjectInterface $subject, string $currency = null): float;
}

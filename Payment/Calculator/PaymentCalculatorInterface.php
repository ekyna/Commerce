<?php

namespace Ekyna\Component\Commerce\Payment\Calculator;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;

/**
 * Interface PaymentCalculatorInterface
 * @package Ekyna\Component\Commerce\Payment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentCalculatorInterface
{
    /**
     * Returns the currency converted payment amount.
     *
     * @param PaymentInterface $payment  The payment
     * @param string           $currency The target currency code.
     *
     * @return float
     */
    public function convertPaymentAmount(PaymentInterface $payment, $currency);

    /**
     * Calculates the paid total.
     *
     * @param PaymentSubjectInterface $subject
     *
     * @return float
     */
    public function calculatePaidTotal(PaymentSubjectInterface $subject);

    /**
     * Calculates the accepted outstanding total.
     *
     * @param PaymentSubjectInterface $subject
     *
     * @return float
     */
    public function calculateOutstandingAcceptedTotal(PaymentSubjectInterface $subject);

    /**
     * Calculates the expired outstanding total.
     *
     * @param PaymentSubjectInterface $subject
     *
     * @return float
     */
    public function calculateOutstandingExpiredTotal(PaymentSubjectInterface $subject);

    /**
     * Calculates the refunded total.
     *
     * @param PaymentSubjectInterface $subject
     *
     * @return float
     */
    public function calculateRefundedTotal(PaymentSubjectInterface $subject);

    /**
     * Calculates the failed total.
     *
     * @param PaymentSubjectInterface $subject
     *
     * @return float
     */
    public function calculateFailedTotal(PaymentSubjectInterface $subject);

    /**
     * Calculates the canceled total.
     *
     * @param PaymentSubjectInterface $subject
     *
     * @return float
     */
    public function calculateCanceledTotal(PaymentSubjectInterface $subject);

    /**
     * Calculates the offline pending total.
     *
     * @param PaymentSubjectInterface $subject
     *
     * @return float
     */
    public function calculateOfflinePendingTotal(PaymentSubjectInterface $subject);
}

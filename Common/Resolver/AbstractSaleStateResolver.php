<?php

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class AbstractSaleStateResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleStateResolver implements StateResolverInterface
{
    /**
     * Resolves the sale payment state.
     *
     * @param Model\SaleInterface $sale
     *
     * @return string
     */
    protected function resolvePaymentsState(Model\SaleInterface $sale)
    {
        $paidTotal = $refundTotal = $failedTotal = $outstandingAmount = $offlinePendingAmount = 0;

        $payments = $sale->getPayments();
        if (0 < $payments->count()) {
            // Gather state amounts
            foreach ($payments as $payment) {
                // TODO Deal with payment currency conversion ...
                if ($payment->getState() === PaymentStates::STATE_CAPTURED) {
                    $paidTotal += $payment->getAmount();
                    if ($payment->getMethod()->isOutstanding()) {
                        $outstandingAmount += $payment->getAmount();
                    }
                } else if ($payment->getState() === PaymentStates::STATE_AUTHORIZED) {
                    $paidTotal += $payment->getAmount();
                    if ($payment->getMethod()->isOutstanding()) {
                        $outstandingAmount += $payment->getAmount();
                    }
                } else if ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                    $refundTotal += $payment->getAmount();
                } else if ($payment->getState() === PaymentStates::STATE_FAILED) {
                    $failedTotal += $payment->getAmount();
                } else if($payment->getState() === PaymentStates::STATE_PENDING && $payment->getMethod()->isManual()) {
                    $offlinePendingAmount += $payment->getAmount();
                }
            }

            $granTotal = $sale->getGrandTotal();
            $currency = $sale->getCurrency();

            // Outstanding case
            if (0 < $outstandingAmount && null !== $date = $sale->getOutstandingDate()) {
                $today = new \DateTime();
                // If payment limit date is past
                if ($today > $date) {
                    $paidTotal -= $outstandingAmount;
                } else {
                    $outstandingAmount = 0;
                }
            }

            // State by amounts
            if (0 <= Money::compare($paidTotal, $granTotal, $currency)) {
                // PAID total is greater than or equal the sale total
                return PaymentStates::STATE_CAPTURED;
            } elseif (0 < $outstandingAmount) {
                // OUTSTANDING total is greater than zero
                return PaymentStates::STATE_OUTSTANDING;
            } elseif (0 < $paidTotal + $offlinePendingAmount) {
                // PENDING total is greater than zero
                return PaymentStates::STATE_PENDING;
            } elseif (0 <= Money::compare($refundTotal, $granTotal, $currency)) {
                // REFUNDED total is greater than or equal the sale total
                return PaymentStates::STATE_REFUNDED;
            } elseif (0 <= Money::compare($failedTotal, $granTotal, $currency)) {
                // FAILED total is greater than or equal the sale total
                return PaymentStates::STATE_FAILED;
            }
        }

        return PaymentStates::STATE_NEW;
    }
}

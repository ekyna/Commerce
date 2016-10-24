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
        $capturedTotal = $authorizedTotal = $refundTotal = $failedTotal = 0;

        $payments = $sale->getPayments();
        if (0 < $payments->count()) {
            // Gather state amounts
            foreach ($payments as $payment) {
                // TODO Deal with payment currency conversion ...
                if ($payment->getState() == PaymentStates::STATE_CAPTURED) {
                    $capturedTotal += $payment->getAmount();
                } else if ($payment->getState() == PaymentStates::STATE_AUTHORIZED) {
                    $authorizedTotal += $payment->getAmount();
                } else if ($payment->getState() == PaymentStates::STATE_REFUNDED) {
                    $refundTotal += $payment->getAmount();
                } else if ($payment->getState() == PaymentStates::STATE_FAILED) {
                    $failedTotal += $payment->getAmount();
                }
            }

            $granTotal = $sale->getGrandTotal();

            $currency = $sale->getCurrency(); // TODO from sale's currency

            // State by amounts
            if (0 <= Money::compare($capturedTotal, $granTotal, $currency)) {
                return PaymentStates::STATE_CAPTURED;
            } elseif (0 <= Money::compare($authorizedTotal + $capturedTotal, $granTotal, $currency)) {
                return PaymentStates::STATE_AUTHORIZED;
            } elseif (0 <= Money::compare($refundTotal, $granTotal, $currency)) {
                return PaymentStates::STATE_REFUNDED;
            } elseif (0 <= Money::compare($failedTotal, $granTotal, $currency)) {
                return PaymentStates::STATE_FAILED;
            }

            // Check for offline pending payment
            foreach ($payments as $payment) {
                if (in_array($payment->getState(), [PaymentStates::STATE_PENDING])
                    && $payment->getMethod()->isManual()) {
                    return PaymentStates::STATE_PENDING;
                }
            }
        }

        return PaymentStates::STATE_NEW;
    }
}

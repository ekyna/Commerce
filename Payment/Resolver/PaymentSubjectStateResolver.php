<?php

namespace Ekyna\Component\Commerce\Payment\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;

/**
 * Class PaymentSubjectStateResolver
 * @package Ekyna\Component\Commerce\Payment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentSubjectStateResolver implements StateResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve($subject)
    {
        if (!$subject instanceof PaymentSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . PaymentSubjectInterface::class);
        }

        $payments = $subject->getPayments();
        if (0 === $payments->count()) {
            return $this->setState($subject, PaymentStates::STATE_NEW);
        }

        $paidTotal = $refundTotal = $failedTotal = $outstandingAmount = $offlinePendingAmount = 0;

        // Gather state amounts
        foreach ($payments as $payment) {
            // TODO Deal with payment currency conversion ...
            if (PaymentStates::isPaidState($payment->getState())) {
                $paidTotal += $payment->getAmount();
                if ($payment->getMethod()->isOutstanding()) {
                    $outstandingAmount += $payment->getAmount();
                }
            } elseif ($payment->getState() === PaymentStates::STATE_REFUNDED) {
                $refundTotal += $payment->getAmount();
            } elseif ($payment->getState() === PaymentStates::STATE_FAILED) {
                $failedTotal += $payment->getAmount();
            } elseif($payment->getState() === PaymentStates::STATE_PENDING && $payment->getMethod()->isManual()) {
                $offlinePendingAmount += $payment->getAmount();
            }
        }

        // Outstanding case
        if (0 < $outstandingAmount && null !== $date = $subject->getOutstandingDate()) {
            $today = new \DateTime();
            $today->setTime(0, 0, 0);
            // If payment limit date is past
            if ($today > $date) {
                $paidTotal -= $outstandingAmount;
            } else {
                $outstandingAmount = 0;
            }
        }

        $granTotal = $subject->getGrandTotal();
        $currency = $subject->getCurrency()->getCode();

        // State by amounts
        if (0 <= Money::compare($paidTotal, $granTotal, $currency)) {
            // PAID total is greater than or equal the sale total
            return $this->setState($subject, PaymentStates::STATE_CAPTURED);
        } elseif (0 < $outstandingAmount) {
            // OUTSTANDING total is greater than zero
            return $this->setState($subject, PaymentStates::STATE_OUTSTANDING);
        } elseif (0 < $paidTotal + $offlinePendingAmount) {
            // PENDING total is greater than zero
            return $this->setState($subject, PaymentStates::STATE_PENDING);
        } elseif (0 <= Money::compare($refundTotal, $granTotal, $currency)) {
            // REFUNDED total is greater than or equal the sale total
            return $this->setState($subject, PaymentStates::STATE_REFUNDED);
        } elseif (0 <= Money::compare($failedTotal, $granTotal, $currency)) {
            // FAILED total is greater than or equal the sale total
            return $this->setState($subject, PaymentStates::STATE_FAILED);
        }

        // NEW (default) state
        return $this->setState($subject, PaymentStates::STATE_PENDING);
    }

    /**
     * Sets the payment state.
     *
     * @param PaymentSubjectInterface $subject
     * @param string                  $state
     *
     * @return bool Whether the shipment state has been updated.
     */
    protected function setState(PaymentSubjectInterface $subject, $state)
    {
        if ($state !== $subject->getPaymentState()) {
            $subject->setPaymentState($state);

            return true;
        }

        return false;
    }
}

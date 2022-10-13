<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;

/**
 * Class PaymentCalculator
 * @package Ekyna\Component\Commerce\Payment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentCalculator implements PaymentCalculatorInterface
{
    protected AmountCalculatorFactory    $calculatorFactory;
    protected CurrencyConverterInterface $currencyConverter;
    protected string                     $currency;

    public function __construct(
        AmountCalculatorFactory    $calculatorFactory,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->calculatorFactory = $calculatorFactory;
        $this->currencyConverter = $currencyConverter;
        $this->currency = $currencyConverter->getDefaultCurrency();
    }

    public function getPaymentAmounts(PaymentSubjectInterface $subject, string $currency = null): array
    {
        $currency = $currency ?? $subject->getCurrency()->getCode();

        if ($currency === $this->currency) {
            $total = $subject->getGrandTotal();
            $paid = $subject->getPaidTotal();
            $refunded = $subject->getRefundedTotal();
            $deposit = $subject->getDepositTotal();
            $pending = $subject->getPendingTotal();
        } elseif ($subject instanceof SaleInterface) {
            $total = $this->calculatorFactory->create($currency)->calculateSale($subject)->getTotal();
            $paid = $this->calculatePaidTotal($subject, $currency);
            $refunded = $this->calculateRefundedTotal($subject, $currency);
            $deposit = $this->currencyConverter->convertWithSubject($subject->getDepositTotal(), $subject, $currency);
            $pending = $this->calculateOfflinePendingTotal($subject, $currency);
        } else {
            throw new UnexpectedValueException();
        }

        return [
            'total'    => $total,
            'paid'     => $paid,
            'refunded' => $refunded,
            'pending'  => $pending,
            'deposit'  => $deposit,
        ];
    }

    public function calculatePaidTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal
    {
        $currency = $currency ?? $this->currency;

        $total = new Decimal(0);

        // Sum of payments with ACCEPTED states, excluding outstanding method.
        foreach ($subject->getPayments(true) as $payment) {
            if ($payment->getMethod()->isOutstanding()) {
                continue;
            }

            if (!PaymentStates::isPaidState($payment, true)) {
                continue;
            }

            $total += $this->getAmount($payment, $currency);
        }

        return $total;
    }

    public function calculateRefundedTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal
    {
        $currency = $currency ?? $this->currency;

        $total = new Decimal(0);

        // Sum of payments with REFUND state, excluding outstanding method.
        foreach ($subject->getPayments(true) as $payment) {
            if ($payment->getMethod()->isOutstanding()) {
                continue;
            }

            if (PaymentStates::STATE_REFUNDED !== $payment->getState()) {
                continue;
            }

            $total += $this->getAmount($payment, $currency);
        }

        // Sum of refunds with ACCEPTED states, excluding outstanding method.
        foreach ($subject->getPayments(false) as $payment) {
            if ($payment->getMethod()->isOutstanding()) {
                continue;
            }

            if (!PaymentStates::isPaidState($payment)) {
                continue;
            }

            $total += $this->getAmount($payment, $currency);
        }

        return $total;
    }

    public function calculateOutstandingAcceptedTotal(
        PaymentSubjectInterface $subject,
        string                  $currency = null
    ): Decimal {
        $currency = $currency ?? $this->currency;

        $total = new Decimal(0);

        foreach ($subject->getPayments() as $payment) {
            if (!$payment->getMethod()->isOutstanding()) {
                continue;
            }

            if ($payment->isRefund()) {
                throw new RuntimeException('Outstanding payment should not be refunded');
            }

            if (!PaymentStates::isPaidState($payment)) {
                continue;
            }

            $total += $this->getAmount($payment, $currency);
        }

        return $total;
    }

    public function calculateOutstandingExpiredTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal
    {
        $currency = $currency ?? $this->currency;

        $total = new Decimal(0);

        foreach ($subject->getPayments() as $payment) {
            if (!$payment->getMethod()->isOutstanding()) {
                continue;
            }

            if ($payment->isRefund()) {
                throw new RuntimeException('Outstanding payment should not be refunded');
            }

            if ($payment->getState() !== PaymentStates::STATE_EXPIRED) {
                continue;
            }

            $total += $this->getAmount($payment, $currency);
        }

        return $total;
    }

    public function calculateFailedTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal
    {
        $currency = $currency ?? $this->currency;

        return $this->calculateTotalByState($subject, PaymentStates::STATE_FAILED, $currency);
    }

    public function calculateCanceledTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal
    {
        $currency = $currency ?? $this->currency;

        return $this->calculateTotalByState($subject, PaymentStates::STATE_CANCELED, $currency, true);
    }

    public function calculateOfflinePendingTotal(PaymentSubjectInterface $subject, string $currency = null): Decimal
    {
        $currency = $currency ?? $this->currency;

        $total = new Decimal(0);

        foreach ($subject->getPayments(true) as $payment) {
            if (!$payment->getMethod()->isManual()) {
                continue;
            }

            if ($payment->getState() !== PaymentStates::STATE_PENDING) {
                continue;
            }

            $total += $this->getAmount($payment, $currency);
        }

        return $total;
    }

    public function calculateExpectedPaymentAmount(PaymentSubjectInterface $subject, string $currency = null): Decimal
    {
        [
            'total'    => $total,
            'paid'     => $paid,
            'refunded' => $refunded,
            'pending'  => $pending,
            'deposit'  => $deposit,
        ] = $this->getPaymentAmounts($subject, $currency);

        $paid -= $refunded;

        $currency = $currency ?? $subject->getCurrency()->getCode();

        if ($currency === $this->currency) {
            $outstanding = $subject->getOutstandingAccepted();
        } elseif ($subject instanceof SaleInterface) {
            $outstanding = $this->calculateOutstandingAcceptedTotal($subject, $currency);
        } else {
            throw new UnexpectedValueException();
        }

        // If subject has deposit
        if (0 < $deposit) {
            //if (0 <= Money::compare($paid, $deposit, $currency)) {
            if ($paid >= $deposit) {
                // If paid greater than or equal deposit
                $total -= $deposit;
                $paid -= $deposit;
            } elseif ($pending >= $deposit) {
                // If pending greater than or equal deposit
                $total -= $deposit;
                $pending -= $deposit;
            } else {
                // Else pay deposit
                $total = $deposit;
            }
        }

        $amount = new Decimal(0);
        $p = $total->compareTo($paid + $pending + $outstanding);

        // If (paid total + pending total + accepted outstanding) is lower than total
        if (1 === $p) {
            // Pay difference
            $amount = $total - $paid - $pending - $outstanding;
        } elseif (0 === $p && 0 < $outstanding) {
            // Pay outstanding
            $amount = $outstanding;
        } elseif (-1 === $p) {
            $amount = $total - $paid;
        }

        if (0 < $amount) {
            return Money::round($amount, $currency);
        }

        return new Decimal(0);
    }

    public function calculateExpectedRefundAmount(PaymentSubjectInterface $subject, string $currency = null): Decimal
    {
        [
            'total'    => $total,
            'paid'     => $paid,
            'refunded' => $refunded,
        ] = $this->getPaymentAmounts($subject, $currency);

        $paid -= $refunded;

        $currency = $currency ?? $subject->getCurrency()->getCode();

        if ($paid > $total) {
            return Money::round($paid - $total, $currency);
        }

        return new Decimal(0);
    }

    /**
     * Calculates the payments total by state.
     */
    protected function calculateTotalByState(
        PaymentSubjectInterface $subject,
        string                  $state,
        string                  $currency,
        bool                    $outstanding = false
    ): Decimal {
        PaymentStates::isValidState($state);

        $total = new Decimal(0);

        foreach ($subject->getPayments(true) as $payment) {
            // Skip outstanding payments
            if (!$outstanding && $payment->getMethod()->isOutstanding()) {
                continue;
            }

            // If payment has the expected state
            if ($payment->getState() === $state) {
                $total += $this->getAmount($payment, $currency);
            }
        }

        return $total;
    }

    /**
     * Returns the payment amount in the given currency.
     */
    protected function getAmount(PaymentInterface $payment, string $currency): Decimal
    {
        $pc = $payment->getCurrency()->getCode();

        if ($currency === $pc) {
            return Money::round($payment->getAmount(), $currency);
        }

        $rate = $this
            ->currencyConverter
            ->getSubjectExchangeRate($payment->getSale(), $pc, $currency);

        return $this
            ->currencyConverter
            ->convertWithRate($payment->getAmount(), $rate, $pc, false);
    }
}

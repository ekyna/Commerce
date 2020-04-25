<?php

namespace Ekyna\Component\Commerce\Payment\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
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
    /**
     * @var AmountCalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var string
     */
    protected $currency;


    /**
     * Constructor.
     *
     * @param AmountCalculatorFactory    $calculatorFactory
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(
        AmountCalculatorFactory $calculatorFactory,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->calculatorFactory = $calculatorFactory;
        $this->currencyConverter = $currencyConverter;
        $this->currency = $currencyConverter->getDefaultCurrency();
    }

    /**
     * @inheritDoc
     */
    public function getPaymentAmounts(PaymentSubjectInterface $subject, string $currency = null): array
    {
        $currency = $currency ?? $subject->getCurrency()->getCode();

        if ($currency === $this->currency) {
            if ($subject instanceof InvoiceSubjectInterface && $subject->hasInvoices()) {
                $total = $subject->getInvoiceTotal() - $subject->getCreditTotal();
            } else {
                $total = $subject->getGrandTotal();
            }
            $paid = $subject->getPaidTotal();
            $refunded = $subject->getRefundedTotal();
            $deposit = $subject->getDepositTotal();
            $pending = $subject->getPendingTotal();
        } elseif ($subject instanceof SaleInterface) {
            if ($subject instanceof InvoiceSubjectInterface && $subject->isFullyInvoiced()) {
                // TODO Use invoice calculator ?
                $total = $this->currencyConverter->convertWithSubject(
                    $subject->getInvoiceTotal() - $subject->getCreditTotal(), $subject, $currency
                );
            } else {
                $total = $this->calculatorFactory->create($currency)->calculateSale($subject)->getTotal();
            }
            $paid = $this->calculatePaidTotal($subject, $currency);
            $refunded = $this->calculateRefundedTotal($subject, $currency);
            $deposit = $this->currencyConverter->convertWithSubject($subject->getDepositTotal(), $subject, $currency);
            $pending = $this->calculateOfflinePendingTotal($subject, $currency);
        } else {
            throw new UnexpectedValueException();
        }

        return [$total, $paid, $refunded, $deposit, $pending];
    }

    /**
     * @inheritDoc
     */
    public function calculatePaidTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        $total = 0;

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

    /**
     * @inheritDoc
     */
    public function calculateRefundedTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        $total = 0;

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

    /**
     * @inheritDoc
     */
    public function calculateOutstandingAcceptedTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
            if (!$payment->getMethod()->isOutstanding()) {
                continue;
            }

            if ($payment->isRefund()) {
                throw new RuntimeException("Outstanding payment should not be refunded");
            }

            if (!PaymentStates::isPaidState($payment)) {
                continue;
            }

            $total += $this->getAmount($payment, $currency);
        }

        return $total;
    }

    /**
     * @inheritDoc
     */
    public function calculateOutstandingExpiredTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
            if (!$payment->getMethod()->isOutstanding()) {
                continue;
            }

            if ($payment->isRefund()) {
                throw new RuntimeException("Outstanding payment should not be refunded");
            }

            if ($payment->getState() !== PaymentStates::STATE_EXPIRED) {
                continue;
            }

            $total += $this->getAmount($payment, $currency);
        }

        return $total;
    }

    /**
     * @inheritDoc
     */
    public function calculateFailedTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        return $this->calculateTotalByState($subject, PaymentStates::STATE_FAILED, $currency);
    }

    /**
     * @inheritDoc
     */
    public function calculateCanceledTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        return $this->calculateTotalByState($subject, PaymentStates::STATE_CANCELED, $currency);
    }

    /**
     * @inheritDoc
     */
    public function calculateOfflinePendingTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        $total = 0;

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

    /**
     * @inheritDoc
     */
    public function calculateExpectedPaymentAmount(PaymentSubjectInterface $subject, string $currency = null): float
    {
        [$total, $paid, $refunded, $deposit, $pending] = $this->getPaymentAmounts($subject, $currency);

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
        if (1 === Money::compare($deposit, 0, $currency)) {
            if (0 <= Money::compare($paid, $deposit, $currency)) {
                // If paid greater than or equal deposit
                $total -= $deposit;
                $paid -= $deposit;
            } elseif (0 <= Money::compare($pending, $deposit, $currency)) {
                // If pending greater than or equal deposit
                $total -= $deposit;
                $pending -= $deposit;
            } else {
                // Else pay deposit
                $total = $deposit;
            }
        }

        $amount = 0;
        $p = Money::compare($total, $paid + $pending + $outstanding, $currency);

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

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function calculateExpectedRefundAmount(PaymentSubjectInterface $subject, string $currency = null): float
    {
        [$total, $paid, $refunded] = $this->getPaymentAmounts($subject, $currency);

        $paid -= $refunded;

        $currency = $currency ?? $subject->getCurrency()->getCode();

        if (1 === Money::compare($paid, $total, $currency)) {
            return Money::round($paid - $total, $currency);
        }

        return 0;
    }

    /**
     * Calculates the payments total by state.
     *
     * @param PaymentSubjectInterface $subject
     * @param string                  $state
     * @param string                  $currency
     * @param bool                    $refund
     *
     * @return float
     */
    protected function calculateTotalByState(
        PaymentSubjectInterface $subject,
        string $state,
        string $currency,
        bool $refund = false
    ): float {
        PaymentStates::isValidState($state, true);

        $total = 0;

        foreach ($subject->getPayments(!$refund) as $payment) {
            // Skip outstanding payments
            if ($payment->getMethod()->isOutstanding()) {
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
     *
     * @param PaymentInterface $payment
     * @param string           $currency
     *
     * @return float
     */
    protected function getAmount(PaymentInterface $payment, string $currency): float
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

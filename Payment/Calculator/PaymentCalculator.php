<?php

namespace Ekyna\Component\Commerce\Payment\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
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
    /**
     * @var AmountCalculatorInterface
     */
    protected $amountCalculator;

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
     * @param AmountCalculatorInterface  $amountCalculator
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(
        AmountCalculatorInterface $amountCalculator,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->amountCalculator = $amountCalculator;
        $this->currencyConverter = $currencyConverter;
        $this->currency = $currencyConverter->getDefaultCurrency();
    }

    /**
     * @inheritDoc
     */
    public function calculatePaidTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
            if ($payment->getMethod()->isOutstanding()) {
                continue;
            }

            if (!PaymentStates::isPaidState($payment->getState())) {
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

            if (!PaymentStates::isPaidState($payment->getState())) {
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
    public function calculateRefundedTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $this->currency;

        return $this->calculateTotalByState($subject, PaymentStates::STATE_REFUNDED, $currency);
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

        foreach ($subject->getPayments() as $payment) {
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
    public function calculateRemainingTotal(PaymentSubjectInterface $subject, string $currency = null): float
    {
        $currency = $currency ?? $subject->getCurrency()->getCode();

        if ($currency === $this->currency) {
            $total = $subject->getGrandTotal();
            $deposit = $subject->getDepositTotal();
            $paid = $subject->getPaidTotal();
            $pending = $subject->getPendingTotal();
            $outstanding = $subject->getOutstandingAccepted();
        } elseif ($subject instanceof SaleInterface) {
            $total = $this->amountCalculator->calculateSale($subject, $currency)->getTotal();
            $deposit = $this->currencyConverter->convertWithSubject($subject->getDepositTotal(), $subject, $currency);

            $paid = $this->calculatePaidTotal($subject, $currency);
            $pending = $this->calculateOfflinePendingTotal($subject, $currency);
            $outstanding = $this->calculateOutstandingAcceptedTotal($subject, $currency);
        } else {
            throw new UnexpectedValueException();
        }

        // If subject has deposit
        if (1 === Money::compare($deposit, 0, $currency)) {
            // If paid greater than or equal deposit
            if (0 <= Money::compare($paid, $deposit, $currency)) {
                $total -= $deposit;
                $paid -= $deposit;
            } // If pending greater than or equal deposit
            elseif (0 <= Money::compare($pending, $deposit, $currency)) {
                $total -= $deposit;
                $pending -= $deposit;
            } // Else pay deposit
            else {
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
        }

        if (0 < $amount) {
            return Money::round($amount, $currency);
        }

        return 0;
    }

    /**
     * Calculates the payments total by state.
     *
     * @param PaymentSubjectInterface $subject
     * @param string                  $state
     * @param string                  $currency
     *
     * @return float
     */
    protected function calculateTotalByState(PaymentSubjectInterface $subject, string $state, string $currency): float
    {
        PaymentStates::isValidState($state, true);

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
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

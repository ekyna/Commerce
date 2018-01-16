<?php

namespace Ekyna\Component\Commerce\Payment\Calculator;

use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
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
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;


    /**
     * Constructor.
     *
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(CurrencyConverterInterface $currencyConverter)
    {
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @inheritdoc
     */
    public function convertPaymentAmount(PaymentInterface $payment, $currency)
    {
        return $this->currencyConverter->convert(
            $payment->getAmount(),
            $payment->getCurrency()->getCode(),
            $currency,
            $payment->getCreatedAt()
        );
    }

    /**
     * @inheritdoc
     */
    public function calculatePaidTotal(PaymentSubjectInterface $subject)
    {
        $currency = $subject->getCurrency()->getCode();

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
            if (!$payment->getMethod()->isOutstanding() && PaymentStates::isPaidState($payment->getState())) {
                $total += $this->convertPaymentAmount($payment, $currency);
            }
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateOutstandingAcceptedTotal(PaymentSubjectInterface $subject)
    {
        $currency = $subject->getCurrency()->getCode();

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
            if ($payment->getMethod()->isOutstanding() && PaymentStates::isPaidState($payment->getState())) {
                $total += $this->convertPaymentAmount($payment, $currency);
            }
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateOutstandingExpiredTotal(PaymentSubjectInterface $subject)
    {
        $currency = $subject->getCurrency()->getCode();

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
            if ($payment->getMethod()->isOutstanding() && $payment->getState() === PaymentStates::STATE_EXPIRED) {
                $total += $this->convertPaymentAmount($payment, $currency);
            }
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateRefundedTotal(PaymentSubjectInterface $subject)
    {
        return $this->calculateTotalByState($subject, PaymentStates::STATE_REFUNDED);
    }

    /**
     * @inheritdoc
     */
    public function calculateFailedTotal(PaymentSubjectInterface $subject)
    {
        return $this->calculateTotalByState($subject, PaymentStates::STATE_FAILED);
    }

    /**
     * @inheritdoc
     */
    public function calculateCanceledTotal(PaymentSubjectInterface $subject)
    {
        return $this->calculateTotalByState($subject, PaymentStates::STATE_CANCELED);
    }

    /**
     * @inheritdoc
     */
    public function calculateOfflinePendingTotal(PaymentSubjectInterface $subject)
    {
        $currency = $subject->getCurrency()->getCode();

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
            if ($payment->getState() === PaymentStates::STATE_PENDING && $payment->getMethod()->isManual()) {
                $total += $this->convertPaymentAmount($payment, $currency);
            }
        }

        return $total;
    }

    /**
     * Calculates the payments total by state.
     *
     * @param PaymentSubjectInterface $subject
     * @param string                  $state
     *
     * @return float
     */
    protected function calculateTotalByState(PaymentSubjectInterface $subject, $state)
    {
        PaymentStates::isValidState($state, true);

        $currency = $subject->getCurrency()->getCode();

        $total = 0;

        foreach ($subject->getPayments() as $payment) {
            if ($payment->getState() === $state) {
                $total += $this->convertPaymentAmount($payment, $currency);
            }
        }

        return $total;
    }

}

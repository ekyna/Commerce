<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractStateResolver;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;

/**
 * Class PaymentSubjectStateResolver
 * @package Ekyna\Component\Commerce\Payment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentSubjectStateResolver extends AbstractStateResolver
{
    public function __construct(
        protected readonly PaymentCalculatorInterface        $paymentCalculator,
        protected readonly InvoiceSubjectCalculatorInterface $invoiceSubjectCalculator,
        protected string                                     $defaultCurrency
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param PaymentSubjectInterface $subject
     */
    public function resolve(object $subject): bool
    {
        $state = $this->resolveState($subject);

        if ($state !== $subject->getPaymentState()) {
            $subject->setPaymentState($state);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @param PaymentSubjectInterface $subject
     */
    protected function resolveState(object $subject): string
    {
        if (!$subject->hasItems()) {
            return PaymentStates::STATE_NEW;
        }

        // This method uses the calculated sale's payment totals.
        // Makes sure to update them before calling this method.
        // -> use SaleUpdater::updateTotals

        [
            'total'    => $total,
            'paid'     => $paid,
            'refunded' => $refunded,
            'pending'  => $pending,
            'deposit'  => $deposit,
        ] = $this->paymentCalculator->getPaymentAmounts($subject, $this->defaultCurrency);

        $payments = $paid->sub($refunded);

        if ($payments->isZero() && $total->isZero()) {
            return PaymentStates::STATE_COMPLETED;
        }

        if ($subject->getCurrency()->getCode() === $this->defaultCurrency) {
            $accepted = $subject->getOutstandingAccepted();
            $expired = $subject->getOutstandingExpired();
            if ($subject instanceof InvoiceSubjectInterface) {
                $invoiced = $subject->getInvoiceTotal();
                $credited = $subject->getCreditTotal();
            } else {
                $invoiced = new Decimal(0);
                $credited = new Decimal(0);
            }
        } elseif ($subject instanceof SaleInterface) {
            $accepted = $this->paymentCalculator->calculateOutstandingAcceptedTotal($subject, $this->defaultCurrency);
            $expired = $this->paymentCalculator->calculateOutstandingExpiredTotal($subject, $this->defaultCurrency);
            if ($subject instanceof InvoiceSubjectInterface) {
                $invoiced = $this->invoiceSubjectCalculator->calculateInvoiceTotal($subject, $this->defaultCurrency);
                $credited = $this->invoiceSubjectCalculator->calculateCreditTotal($subject, $this->defaultCurrency);
            } else {
                $invoiced = new Decimal(0);
                $credited = new Decimal(0);
            }
        } else {
            throw new UnexpectedValueException();
        }

        if ($expired->isZero() && $accepted->isZero() && !$paid->isZero()) {
            if (
                $subject instanceof InvoiceSubjectInterface
                && (
                    $total <= $invoiced
                    || $subject->getInvoiceState() === InvoiceStates::STATE_COMPLETED
                    || !$refunded->isZero()
                    || !$credited->isZero()
                )
            ) {
                $total = $invoiced->sub($credited);

                if ($total->equals($payments)) {
                    return $payments->isZero() ? PaymentStates::STATE_REFUNDED : PaymentStates::STATE_COMPLETED;
                }
            }

            if (!$invoiced->isZero() || !$refunded->isZero()) {
                return PaymentStates::STATE_CAPTURED;
            }
        }

        // CAPTURED paid total plus accepted outstanding total is greater than grand total
        if ($total <= $payments->add($accepted)) { // TODO Test with refund
            return PaymentStates::STATE_CAPTURED;
        }

        // OUTSTANDING expired total is greater than zero
        if (!$expired->isZero()) {
            return PaymentStates::STATE_OUTSTANDING;
        }

        // DEPOSIT paid total is greater than deposit total
        if (!$deposit->isZero() && $deposit <= $payments) { // TODO Test with refund
            return PaymentStates::STATE_DEPOSIT;
        }

        if (!$pending->isZero()) {
            // PENDING total is greater than zero
            if ($total <= $payments->add($accepted)->add($pending)) {
                return PaymentStates::STATE_PENDING;
            }

            // PENDING pending total is greater than deposit total
            if ($payments->isZero() && !$deposit->isZero() && $deposit <= $pending) {
                return PaymentStates::STATE_PENDING;
            }
        }

        // FAILED total is greater than or equals the grand total
        if ($total <= $this->paymentCalculator->calculateFailedTotal($subject, $this->defaultCurrency)) {
            return PaymentStates::STATE_FAILED;
        }

        // CANCELED total is greater than or equals the grand total
        if ($total <= $this->paymentCalculator->calculateCanceledTotal($subject, $this->defaultCurrency)) {
            return PaymentStates::STATE_CANCELED;
        }

        // NEW by default
        return PaymentStates::STATE_NEW;
    }

    protected function supports(object $subject): void
    {
        if (!$subject instanceof PaymentSubjectInterface) {
            throw new UnexpectedTypeException($subject, PaymentSubjectInterface::class);
        }
    }
}

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
        if (!$subject->hasPayments()) {
            // CANCELED subject is invoiceable and is fully credited
            if (
                $subject instanceof InvoiceSubjectInterface && $subject->isFullyInvoiced()
                && $subject->getInvoiceState() === InvoiceStates::STATE_CREDITED
            ) {
                return PaymentStates::STATE_CANCELED;
            }

            // NEW by default
            return PaymentStates::STATE_NEW;
        }

        // This method uses the calculated sale's payment totals.
        // Makes sure to update them before calling this method.
        // -> use SaleUpdater::updateTotals

        $currency = $subject->getCurrency()->getCode();

        [
            'total'    => $total,
            'paid'     => $paid,
            'refunded' => $refunded,
            'pending'  => $pending,
            'deposit'  => $deposit,
        ] = $this->paymentCalculator->getPaymentAmounts($subject, $currency);

        if ($currency === $this->defaultCurrency) {
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
            $accepted = $this->paymentCalculator->calculateOutstandingAcceptedTotal($subject, $currency);
            $expired = $this->paymentCalculator->calculateOutstandingExpiredTotal($subject, $currency);
            if ($subject instanceof InvoiceSubjectInterface) {
                $invoiced = $this->invoiceSubjectCalculator->calculateInvoiceTotal($subject, $currency);
                $credited = $this->invoiceSubjectCalculator->calculateCreditTotal($subject, $currency);
            } else {
                $invoiced = new Decimal(0);
                $credited = new Decimal(0);
            }
        } else {
            throw new UnexpectedValueException();
        }

        // COMPLETED paid total equals grand total and no accepted/expired outstanding
        if (0 < $paid && $accepted->isZero() && $expired->isZero()) {
            // If invoice subject and fully invoiced (ignoring credits)
            if (0 < $invoiced) {
                if ($paid->sub($refunded)->equals($invoiced->sub($credited))) {
                    // REFUNDED if credited, else COMPLETED
                    return 0 < $credited ? PaymentStates::STATE_REFUNDED : PaymentStates::STATE_COMPLETED;
                }

                // CAPTURED
                return PaymentStates::STATE_CAPTURED;
            }

            if ($paid->sub($refunded)->isZero()) {
                // REFUNDED
                return PaymentStates::STATE_REFUNDED;
            }

            if ($paid->sub($refunded)->equals($total)) {
                // COMPLETED
                return PaymentStates::STATE_COMPLETED;
            }
        }

        $fullFill = function (Decimal $amount) use ($total): bool {
            return $amount >= $total;
        };

        // CAPTURED paid total plus accepted outstanding total is greater than grand total
        if ((0 < $paid || 0 < $accepted) && $fullFill($paid + $accepted)) {
            return PaymentStates::STATE_CAPTURED;
        }

        // DEPOSIT paid total is greater than deposit total
        if (0 < $paid && 0 < $deposit && $paid >= $deposit) {
            return PaymentStates::STATE_DEPOSIT;
        }

        // OUTSTANDING expired total is greater than zero
        if (0 < $expired) {
            return PaymentStates::STATE_OUTSTANDING;
        }

        // PENDING pending total is greater than deposit total
        if ($paid->isZero() && 0 < $pending && 0 < $deposit && $pending >= $deposit) {
            return PaymentStates::STATE_PENDING;
        }

        // PENDING total is greater than zero
        if ((0 < $paid || 0 < $accepted || 0 < $pending) && $fullFill($paid + $accepted + $pending)) {
            return PaymentStates::STATE_PENDING;
        }

        // CANCELED subject has invoice(s) and is fully credited
        if ($subject instanceof InvoiceSubjectInterface) {
            if ($subject->getInvoiceState() === InvoiceStates::STATE_CREDITED) {
                return PaymentStates::STATE_CANCELED;
            }
        }

        // FAILED total is greater than or equals the grand total
        if ($total && $fullFill($this->paymentCalculator->calculateFailedTotal($subject, $currency))) {
            return PaymentStates::STATE_FAILED;
        }

        // CANCELED total is greater than or equals the grand total
        if ($fullFill($this->paymentCalculator->calculateCanceledTotal($subject, $currency))) {
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

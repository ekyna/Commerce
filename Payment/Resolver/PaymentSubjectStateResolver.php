<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractStateResolver;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
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
    protected PaymentCalculatorInterface $paymentCalculator;
    protected CurrencyConverterInterface $currencyConverter;
    protected string                     $defaultCurrency;

    public function __construct(
        PaymentCalculatorInterface $paymentCalculator,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->paymentCalculator = $paymentCalculator;
        $this->currencyConverter = $currencyConverter;
        $this->defaultCurrency = $currencyConverter->getDefaultCurrency();
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
            $total,
            $paid,
            $refunded,
            $deposit,
            $pending,
        ] = $this->paymentCalculator->getPaymentAmounts($subject, $currency);

        if ($currency === $this->defaultCurrency) {
            $accepted = $subject->getOutstandingAccepted();
            $expired = $subject->getOutstandingExpired();
        } elseif ($subject instanceof SaleInterface) {
            $accepted = $this->paymentCalculator->calculateOutstandingAcceptedTotal($subject, $currency);
            $expired = $this->paymentCalculator->calculateOutstandingExpiredTotal($subject, $currency);
        } else {
            throw new UnexpectedValueException();
        }

        // COMPLETED paid total equals grand total and no accepted/expired outstanding
        if (
            $paid && $accepted->isZero() && $expired->isZero() && $paid->sub($refunded)->equals($total)
        ) {
            // If invoice subject and fully invoiced (ignoring credits)
            if ($subject instanceof InvoiceSubjectInterface) {
                if ($subject->isFullyInvoiced()) {
                    // REFUNDED If refunded amount equals total
                    if ($total->isZero() || $refunded->equals($total)) {
                        return PaymentStates::STATE_REFUNDED;
                    }

                    // COMPLETED
                    return PaymentStates::STATE_COMPLETED;
                }

                // ACCEPTED
                return PaymentStates::STATE_CAPTURED;
            }

            // COMPLETED
            return PaymentStates::STATE_COMPLETED;
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

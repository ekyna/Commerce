<?php

namespace Ekyna\Component\Commerce\Payment\Resolver;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractStateResolver;
use Ekyna\Component\Commerce\Common\Util\Money;
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
    /**
     * @var PaymentCalculatorInterface
     */
    protected $paymentCalculator;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param PaymentCalculatorInterface $paymentCalculator
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(
        PaymentCalculatorInterface $paymentCalculator,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->paymentCalculator = $paymentCalculator;
        $this->currencyConverter = $currencyConverter;
        $this->defaultCurrency   = $currencyConverter->getDefaultCurrency();
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
                $subject instanceof InvoiceSubjectInterface
                && ($subject->getInvoiceState() === InvoiceStates::STATE_CREDITED)
            ) {
                return PaymentStates::STATE_CANCELED;
            }

            // NEW by default
            return PaymentStates::STATE_NEW;
        }

        // This method uses the calculated sale's payment totals.
        // Makes sure to update them before calling of this method.
        // -> use SaleUpdater::updateTotals for example

        $currency = $subject->getCurrency()->getCode();
        [$total, $paid, $refunded, $deposit, $pending] = $this->paymentCalculator->getPaymentAmounts($subject, $currency);

        if ($currency === $this->defaultCurrency) {
            $accepted = $subject->getOutstandingAccepted();
            $expired  = $subject->getOutstandingExpired();
        } elseif ($subject instanceof SaleInterface) {
            $accepted = $this->paymentCalculator->calculateOutstandingAcceptedTotal($subject, $currency);
            $expired  = $this->paymentCalculator->calculateOutstandingExpiredTotal($subject, $currency);
        } else {
            throw new UnexpectedValueException();
        }

        // COMPLETED paid total equals grand total and no accepted/expired outstanding
        if (
            $paid && (0 == $accepted) && (0 == $expired) &&
            (0 === Money::compare($paid - $refunded, $total, $currency))
        ) {
            // REFUNDED Fully if refunded amount equals total
            if (0 === Money::compare($refunded, $total, $currency)) {
                return PaymentStates::STATE_REFUNDED;
            }

            return PaymentStates::STATE_COMPLETED;
        }

        $fullFill = function ($amount) use ($total, $currency) {
            return 0 <= Money::compare($amount, $total, $currency);
        };

        // CAPTURED paid total plus accepted outstanding total is greater than grand total
        if (($paid || $accepted) && $fullFill($paid + $accepted)) {
            return PaymentStates::STATE_CAPTURED;
        }

        // DEPOSIT paid total is greater than deposit total
        if ($paid && 0 < $deposit) {
            if (0 <= Money::compare($paid, $deposit, $currency)) {
                return PaymentStates::STATE_DEPOSIT;
            }
        }

        // OUTSTANDING expired total is greater than zero
        if (0 < $expired) {
            return PaymentStates::STATE_OUTSTANDING;
        }

        // PENDING total is greater than zero
        if (($paid || $accepted || $pending) && $fullFill($paid + $accepted + $pending)) {
            return PaymentStates::STATE_PENDING;
        }

        // CANCELED subject has invoice(s) and is fully credited
        if ($subject instanceof InvoiceSubjectInterface) {
            if ($subject->getInvoiceState() === InvoiceStates::STATE_CREDITED) {
                return PaymentStates::STATE_CANCELED;
            }
        }

        // FAILED total is greater than or equals the grand total
        if ($fullFill($this->paymentCalculator->calculateFailedTotal($subject, $currency))) {
            return PaymentStates::STATE_FAILED;
        }

        // CANCELED total is greater than or equals the grand total
        if ($fullFill($this->paymentCalculator->calculateCanceledTotal($subject, $currency))) {
            return PaymentStates::STATE_CANCELED;
        }

        // NEW by default
        return PaymentStates::STATE_NEW;
    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof PaymentSubjectInterface) {
            throw new UnexpectedTypeException($subject, PaymentSubjectInterface::class);
        }
    }
}

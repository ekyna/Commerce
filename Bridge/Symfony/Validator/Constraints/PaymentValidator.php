<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PaymentValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentValidator extends ConstraintValidator
{
    private CurrencyConverterInterface $currencyConverter;

    public function __construct(CurrencyConverterInterface $currencyConverter)
    {
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof PaymentInterface) {
            throw new UnexpectedTypeException($value, PaymentInterface::class);
        }
        if (!$constraint instanceof Payment) {
            throw new UnexpectedTypeException($constraint, Payment::class);
        }

        if (null === $method = $value->getMethod()) {
            return;
        }

        if ($value->isRefund()) {
            if ($method->isOutstanding()) {
                $this
                    ->context
                    ->buildViolation($constraint->refund_outstanding)
                    ->atPath('method')
                    ->addViolation();
            }

            return;
        }

        if ('Checkout' === $this->context->getGroup()) {
            // Abort as we need the sale for further validation, and it's
            // not available when payment is created through CheckoutManager.
            return;
        }

        $this->validateAmount($value);
    }

    /**
     * Validates the payment amount.
     *
     * @param PaymentInterface $payment
     *
     * @throws LogicException
     */
    private function validateAmount(PaymentInterface $payment): void
    {
        $method = $payment->getMethod();

        if (!$method->isOutstanding() && !$method->isCredit()) {
            return;
        }

        if (null === $sale = $payment->getSale()) {
            throw new LogicException('Payment sale must be set.');
        }

        if (null === $customer = $sale->getCustomer()) {
            throw new LogicException('Sale customer must be set.');
        }

        if ($method->isOutstanding()) {
            // If sale has a outstanding limit
            if ((0 < $limit = $sale->getOutstandingLimit()) && $customer->isOutstandingOverflow()) {
                // Use sale's balance
                $available = $limit - $sale->getOutstandingAccepted() - $sale->getOutstandingExpired();
            } else {
                // Use customer's limit and balance
                $available = $customer->getOutstandingLimit() + $customer->getOutstandingBalance();
            }
        } else { // Credit method case
            $available = $customer->getCreditBalance();
        }

        // If payment is paid, add the payment amount
        if (PaymentStates::isPaidState($payment)) {
            $available += $payment->getRealAmount();
        }

        $available = max(new Decimal(0), $available);

        $available = $this->currencyConverter->convertWithSubject($available, $payment);

        $violations = $this
            ->context
            ->getValidator()
            ->validate($payment->getAmount(), [new LessThanOrEqual($available)]);

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $this->context
                ->buildViolation($violation->getMessage(), $violation->getParameters())
                ->atPath('amount')
                ->addViolation();
        }
    }
}

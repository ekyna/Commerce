<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class PaymentValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentValidator extends ConstraintValidator
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
     * @inheritDoc
     */
    public function validate($payment, Constraint $constraint)
    {
        if (!$payment instanceof PaymentInterface) {
            throw new InvalidArgumentException("Expected instance of " . PaymentInterface::class);
        }
        if (!$constraint instanceof Payment) {
            throw new InvalidArgumentException("Expected instance of " . Payment::class);
        }

        if (null === $method = $payment->getMethod()) {
            return;
        }

        if ($payment->isRefund()) {
            if ($method->isOutstanding()) {
                $this
                    ->context
                    ->buildViolation($constraint->refund_outstanding)
                    ->atPath('method')
                    ->addViolation();
            }

            return;
        }

        if (in_array('Checkout', $constraint->groups, true)) {
            // Abort as we need the sale for further validation, and it's
            // not available when payment is created through CheckoutManager.
            return;
        }

        $this->validateAmount($payment);
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
            throw new LogicException("Payment sale must be set.");
        }

        if (null === $customer = $sale->getCustomer()) {
            throw new LogicException("Sale customer must be set.");
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

        $available = max(0, $available);

        $available = $this->currencyConverter->convertWithSubject($available, $payment);

        $violations = $this
            ->context
            ->getValidator()
            ->validate($payment->getAmount(), [new LessThanOrEqual($available)]);

        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $this->context
                ->buildViolation($violation->getMessage(), $violation->getParameters())
                ->atPath('amount')
                ->addViolation();
        }
    }
}

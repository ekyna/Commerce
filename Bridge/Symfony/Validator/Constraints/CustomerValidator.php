<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class CustomerValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($customer, Constraint $constraint)
    {
        if (null === $customer) {
            return;
        }

        if (!$customer instanceof CustomerInterface) {
            throw new InvalidArgumentException('Expected instance of CustomerInterface');
        }
        if (!$constraint instanceof Customer) {
            throw new InvalidArgumentException('Expected instance of Customer (validation constraint)');
        }

        if ($customer->hasParent()) {
            // Prevent hierarchy overflow
            if ($customer->hasChildren() || $customer->getParent()->hasParent()) {
                $this
                    ->context
                    ->buildViolation($constraint->hierarchy_overflow)
                    ->atPath('parent')
                    ->addViolation();
            }

            // Prevent setting a parent to a customer who have non zero outstanding|credit balance
            if (0 != $customer->getOutstandingBalance() || 0 != $customer->getCreditBalance()) {
                $this
                    ->context
                    ->buildViolation($constraint->non_zero_balance)
                    ->atPath('parent')
                    ->addViolation();
            }

            // Child's parent must a have a company name
            if (empty($customer->getParent()->getCompany())) {
                $this
                    ->context
                    ->buildViolation($constraint->parent_company_is_mandatory)
                    ->atPath('parent')
                    ->addViolation();
            }

            // Children can't have a default payment method
            if ($customer->getDefaultPaymentMethod()) {
                $this
                    ->context
                    ->buildViolation($constraint->default_payment_method_must_be_null)
                    ->atPath('defaultPaymentMethod')
                    ->addViolation();
            }

            // Children can't have restricted payment methods
            if (0 < $customer->getPaymentMethods()->count()) {
                $this
                    ->context
                    ->buildViolation($constraint->payment_methods_must_be_empty)
                    ->atPath('paymentMethods')
                    ->addViolation();
            }
        }

        // Parent must have a company name
        if ($customer->hasChildren() && empty($customer->getCompany())) {
            $this
                ->context
                ->buildViolation($constraint->company_is_mandatory)
                ->atPath('company')
                ->addViolation();
        }


        // Outstanding / Payment term
        $hasOutstanding = 0 < $customer->getOutstandingLimit();
        $hasPaymentTerm = null !== $customer->getPaymentTerm();
        if ($hasOutstanding && !$hasPaymentTerm) {
            $this
                ->context
                ->buildViolation($constraint->term_required_for_outstanding)
                ->atPath('paymentTerm')
                ->addViolation();
        } else if ($hasPaymentTerm && !$hasOutstanding) {
            $this
                ->context
                ->buildViolation($constraint->outstanding_required_for_term)
                ->atPath('outstandingLimit')
                ->addViolation();
        }

        if ($default = $customer->getDefaultPaymentMethod()) {
            // Prevent duplicate
            if ($customer->hasPaymentMethod($default)) {
                $this
                    ->context
                    ->buildViolation($constraint->duplicate_payment_method)
                    ->atPath('paymentMethods')
                    ->addViolation();
            }
        } elseif (0 < $customer->getPaymentMethods()->count()) {
            // Customers with restricted payment methods must have a default payment method
            $this
                ->context
                ->buildViolation($constraint->default_payment_method_is_mandatory)
                ->atPath('defaultPaymentMethod')
                ->addViolation();
        }

        // Credit and Outstanding payment methods must not be added to customer
        foreach ($customer->getPaymentMethods() as $method) {
            if ($method->isOutstanding() || $method->isCredit()) {
                $this
                    ->context
                    ->buildViolation($constraint->unexpected_payment_method)
                    ->atPath('paymentMethods')
                    ->addViolation();
            }
        }
    }
}

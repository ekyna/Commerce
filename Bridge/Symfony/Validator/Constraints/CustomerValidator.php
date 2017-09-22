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

        /* @var CustomerInterface $customer */
        /* @var Customer $constraint */

        // Prevent setting a parent to a customer that is already a parent (has children)
        if ($customer->hasParent()) {
            if ($customer->hasChildren() || $customer->getParent()->hasParent()) {
                $this
                    ->context
                    ->buildViolation($constraint->hierarchy_overflow)
                    ->atPath('parent')
                    ->addViolation();
            }
        }

        // A parent must have a company name.
        if ($customer->hasParent() && 0 == strlen($customer->getParent()->getCompany())) {
            $this
                ->context
                ->buildViolation($constraint->parent_company_is_mandatory)
                ->atPath('parent')
                ->addViolation();
        } elseif ($customer->hasChildren() && 0 == strlen($customer->getCompany())) {
            $this
                ->context
                ->buildViolation($constraint->company_is_mandatory)
                ->atPath('company')
                ->addViolation();
        }
    }
}

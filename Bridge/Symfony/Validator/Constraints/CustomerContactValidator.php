<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class CustomerContactValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerContactValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof CustomerContactInterface) {
            throw new UnexpectedTypeException($value, CustomerContactInterface::class);
        }
        if (!$constraint instanceof CustomerContact) {
            throw new UnexpectedTypeException($constraint, CustomerContact::class);
        }

        if (empty($email = $value->getEmail()) || is_null($customer = $value->getCustomer())) {
            return;
        }

        if ($email !== $customer->getEmail()) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->same_as_customer)
            ->atPath('email')
            ->addViolation();
    }
}

<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class CustomerContactValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerContactValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($contact, Constraint $constraint)
    {
        if (null === $contact) {
            return;
        }

        if (!$contact instanceof CustomerContactInterface) {
            throw new InvalidArgumentException('Expected instance of ' . CustomerContactInterface::class);
        }
        if (!$constraint instanceof CustomerContact) {
            throw new InvalidArgumentException('Expected instance of ' . CustomerContact::class);
        }

        if (empty($email = $contact->getEmail()) || is_null($customer = $contact->getCustomer())) {
            return;
        }

        if ($email != $customer->getEmail()) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->same_as_customer)
            ->atPath('email')
            ->addViolation();
    }
}

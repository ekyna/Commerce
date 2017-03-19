<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class AddressValidator
 * @package Ekyna\Bundle\UserBundle\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AddressValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($address, Constraint $constraint)
    {
        if (null === $address) {
            return;
        }

        if (!$address instanceof AddressInterface) {
            throw new UnexpectedTypeException($address, AddressInterface::class);
        }
        if (!$constraint instanceof Address) {
            throw new UnexpectedTypeException($constraint, Address::class);
        }

        /**
         * @var AddressInterface $address
         * @var Address $constraint
         */
        if ($constraint->identity) {
            if (0 === strlen($address->getGender())) {
                $this->context
                    ->buildViolation($constraint->gender_is_mandatory)
                    ->atPath('gender')
                    ->addViolation();
            }
            if (0 === strlen($address->getFirstName())) {
                $this->context
                    ->buildViolation($constraint->first_name_is_mandatory)
                    ->atPath('firstName')
                    ->addViolation();
            }
            if (0 === strlen($address->getLastName())) {
                $this->context
                    ->buildViolation($constraint->last_name_is_mandatory)
                    ->atPath('lastName')
                    ->addViolation();
            }
        }

        if (0 === strlen($address->getCompany()) && $constraint->company) {
            $this->context
                ->buildViolation($constraint->company_is_mandatory)
                ->atPath('company')
                ->addViolation();
        }

        if (0 === strlen($address->getPhone()) && $constraint->phone) {
            $this->context
                ->buildViolation($constraint->phone_is_mandatory)
                ->atPath('phone')
                ->addViolation();
        }

        if (0 === strlen($address->getMobile()) && $constraint->mobile) {
            $this->context
                ->buildViolation($constraint->mobile_is_mandatory)
                ->atPath('mobile')
                ->addViolation();
        }
    }
}

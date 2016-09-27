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
                    ->buildViolation($constraint->genderIsMandatory)
                    ->atPath('gender')
                    ->addViolation();
            }
            if (0 === strlen($address->getFirstName())) {
                $this->context
                    ->buildViolation($constraint->firstNameIsMandatory)
                    ->atPath('firstName')
                    ->addViolation();
            }
            if (0 === strlen($address->getLastName())) {
                $this->context
                    ->buildViolation($constraint->lastNameIsMandatory)
                    ->atPath('lastName')
                    ->addViolation();
            }
        }

        if (0 === strlen($address->getCompany()) && $constraint->company) {
            $this->context
                ->buildViolation($constraint->companyIsMandatory)
                ->atPath('company')
                ->addViolation();
        }

        if (0 === strlen($address->getPhone()) && $constraint->phone) {
            $this->context
                ->buildViolation($constraint->phoneIsMandatory)
                ->atPath('phone')
                ->addViolation();
        }

        if (0 === strlen($address->getMobile()) && $constraint->mobile) {
            $this->context
                ->buildViolation($constraint->mobileIsMandatory)
                ->atPath('mobile')
                ->addViolation();
        }
    }
}

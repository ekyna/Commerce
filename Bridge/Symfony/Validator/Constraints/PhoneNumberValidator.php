<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber as PhoneNumberObject;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Phone number validator.
 */
class PhoneNumberValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     *
     * @var PhoneNumber $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        if (false === $value instanceof PhoneNumberObject) {
            $value = (string) $value;

            try {
                $phoneNumber = $phoneUtil->parse($value, $constraint->defaultRegion);
            } catch (NumberParseException $e) {
                $this->addViolation($value, $constraint);

                return;
            }
        } else {
            $phoneNumber = $value;
            $value = $phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
        }

        if (false === $phoneUtil->isValidNumber($phoneNumber)) {
            $this->addViolation($value, $constraint);

            return;
        }

        $validTypes = array();

        foreach ($constraint->getTypes() as $type) {
            switch ($type) {
                case PhoneNumber::FIXED_LINE:
                    array_push($validTypes, PhoneNumberType::FIXED_LINE, PhoneNumberType::FIXED_LINE_OR_MOBILE);
                    break;
                case PhoneNumber::MOBILE:
                    array_push($validTypes, PhoneNumberType::MOBILE, PhoneNumberType::FIXED_LINE_OR_MOBILE);
                    break;
                case PhoneNumber::PAGER:
                    array_push($validTypes, PhoneNumberType::PAGER);
                    break;
                case PhoneNumber::PERSONAL_NUMBER:
                    array_push($validTypes, PhoneNumberType::PERSONAL_NUMBER);
                    break;
                case PhoneNumber::PREMIUM_RATE:
                    array_push($validTypes, PhoneNumberType::PREMIUM_RATE);
                    break;
                case PhoneNumber::SHARED_COST:
                    array_push($validTypes, PhoneNumberType::SHARED_COST);
                    break;
                case PhoneNumber::TOLL_FREE:
                    array_push($validTypes, PhoneNumberType::TOLL_FREE);
                    break;
                case PhoneNumber::UAN:
                    array_push($validTypes, PhoneNumberType::UAN);
                    break;
                case PhoneNumber::VOIP:
                    array_push($validTypes, PhoneNumberType::VOIP);
                    break;
                case PhoneNumber::VOICEMAIL:
                    array_push($validTypes, PhoneNumberType::VOICEMAIL);
                    break;
            }
        }

        $validTypes = array_unique($validTypes);

        if (count($validTypes)) {
            $type = $phoneUtil->getNumberType($phoneNumber);

            if (false === in_array($type, $validTypes, true)) {
                $this->addViolation($value, $constraint);

                return;
            }
        }
    }

    /**
     * Add a violation.
     *
     * @param mixed      $value      The value that should be validated.
     * @param Constraint $constraint The constraint for the validation.
     */
    private function addViolation($value, Constraint $constraint)
    {
        /** @var \Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber $constraint */
        if ($this->context instanceof ExecutionContextInterface) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ type }}', $constraint->getType())
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(PhoneNumber::INVALID_PHONE_NUMBER_ERROR)
                ->addViolation();
        } else {
            $this->context->addViolation($constraint->getMessage(), array(
                '{{ type }}' => $constraint->getType(),
                '{{ value }}' => $value
            ));
        }
    }
}

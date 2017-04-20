<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class IdentityValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IdentityValidator extends ConstraintValidator
{
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccessor;


    /**
     * @inheritDoc
     */
    public function validate($identity, Constraint $constraint)
    {
        if (null === $identity) {
            return;
        }

        if (!$identity instanceof IdentityInterface) {
            throw new UnexpectedTypeException($constraint, IdentityInterface::class);
        }
        if (!$constraint instanceof Identity) {
            throw new UnexpectedTypeException($constraint, Identity::class);
        }

        // All or none
        $gender = $identity->getGender();
        $firstName = $identity->getFirstName();
        $lastName = $identity->getLastName();

        $all = $gender . $lastName . $firstName;

        if (empty($all)) {
            if ($constraint->required) {
                $this->context
                    ->buildViolation($constraint->mandatory)
                    ->atPath('gender')
                    ->addViolation();
            }
        } else {
            $config = [
                'gender'    => [
                    new Assert\NotBlank(['message' => $constraint->gender_is_mandatory]),
                    new Gender(),
                ],
                'firstName' => [
                    new Assert\NotBlank(['message' => $constraint->first_name_is_mandatory]),
                    new Assert\Length(['min' => 2, 'max' => 32,]),
                ],
                'lastName'  => [
                    new Assert\NotBlank(['message' => $constraint->last_name_is_mandatory]),
                    new Assert\Length(['min' => 2, 'max' => 32,]),
                ],
            ];

            if (null === $this->propertyAccessor) {
                $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
            }

            foreach ($config as $field => $constraints) {
                $value = $this->propertyAccessor->getValue($identity, $field);
                $violationList = $this->context->getValidator()->validate($value, $constraints);

                /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
                foreach ($violationList as $violation) {
                    $this->context
                        ->buildViolation($violation->getMessage())
                        ->atPath($field)
                        ->addViolation();
                }
            }
        }
    }

    /**
     * Validates the identity for the given context.
     *
     * @param ExecutionContextInterface $context
     * @param IdentityInterface         $identity
     * @param array                     $config
     * @param string                    $pathPrefix
     */
    static public function validateIdentity(
        ExecutionContextInterface $context,
        IdentityInterface $identity,
        array $config = [],
        $pathPrefix = null
    ) {
        $violationList = $context->getValidator()->validate($identity, [new Identity($config)]);

        if (!empty($pathPrefix)) {
            $pathPrefix = rtrim($pathPrefix, '.') . '.';
        }

        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
        foreach ($violationList as $violation) {
            $context
                ->buildViolation($violation->getMessage())
                ->atPath($pathPrefix . $violation->getPropertyPath())
                ->addViolation();
        }
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Validator\NotHtml;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class IdentityValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IdentityValidator extends ConstraintValidator
{
    private ?PropertyAccessor $propertyAccessor = null;

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof IdentityInterface) {
            throw new UnexpectedTypeException($constraint, IdentityInterface::class);
        }
        if (!$constraint instanceof Identity) {
            throw new UnexpectedTypeException($constraint, Identity::class);
        }

        // All or none
        $gender = $value->getGender();
        $firstName = $value->getFirstName();
        $lastName = $value->getLastName();

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
                    new Assert\NotBlank([
                        'message' => $constraint->gender_is_mandatory,
                    ]),
                    new Gender(),
                ],
                'firstName' => [
                    new Assert\NotBlank([
                        'message' => $constraint->first_name_is_mandatory,
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 32,
                    ]),
                    new NotHtml(),
                ],
                'lastName'  => [
                    new Assert\NotBlank(['message' => $constraint->last_name_is_mandatory]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 32,
                    ]),
                    new NotHtml(),
                ],
            ];

            if (null === $this->propertyAccessor) {
                $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
            }

            foreach ($config as $field => $constraints) {
                $fieldValue = $this->propertyAccessor->getValue($value, $field);
                $violationList = $this->context->getValidator()->validate($fieldValue, $constraints);

                /** @var ConstraintViolationInterface $violation */
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
     * @param string|null               $pathPrefix
     */
    public static function validateIdentity(
        ExecutionContextInterface $context,
        IdentityInterface         $identity,
        array                     $config = [],
        string                    $pathPrefix = null
    ): void {
        $violationList = $context->getValidator()->validate($identity, [new Identity($config)]);

        if (!empty($pathPrefix)) {
            $pathPrefix = rtrim($pathPrefix, '.') . '.';
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($violationList as $violation) {
            $context
                ->buildViolation($violation->getMessage())
                ->atPath($pathPrefix . $violation->getPropertyPath())
                ->addViolation();
        }
    }
}

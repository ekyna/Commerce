<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class StockSubjectValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectValidator extends ConstraintValidator
{
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccessor;


    /**
     * @inheritdoc
     */
    public function validate($stockSubject, Constraint $constraint)
    {
        if (null === $stockSubject) {
            return;
        }

        if (!$stockSubject instanceof StockSubjectInterface) {
            throw new UnexpectedTypeException($stockSubject, StockSubjectInterface::class);
        }
        if (!$constraint instanceof StockSubject) {
            throw new UnexpectedTypeException($constraint, StockSubject::class);
        }

        $config = [
            'stockMode'              => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'callback' => [StockSubjectModes::class, 'getModes'],
                ]),
            ],
            'stockState'             => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'callback' => [StockSubjectStates::class, 'getStates'],
                ]),
            ],
            'stockFloor'             => [
                new Assert\NotNull(),
                new Assert\GreaterThanOrEqual(['value' => 0]),
            ],
            'minimumOrderQuantity'   => [
                new Assert\NotNull(),
                new Assert\GreaterThanOrEqual(['value' => 0]),
            ],
            'inStock'                => [
                new Assert\NotNull(),
                new Assert\GreaterThanOrEqual(['value' => 0]),
            ],
            'availableStock'         => [
                new Assert\NotNull(),
                new Assert\GreaterThanOrEqual(['value' => 0]),
            ],
            'virtualStock'           => [
                new Assert\NotNull(),
                new Assert\NotNull(),
            ],
            'replenishmentTime'      => [
                new Assert\NotNull(),
                new Assert\GreaterThanOrEqual(['value' => 0]),
            ],
            'estimatedDateOfArrival' => [
                new Assert\DateTime(),
            ],
        ];

        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        foreach ($config as $field => $constraints) {
            $value = $this->propertyAccessor->getValue($stockSubject, $field);
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

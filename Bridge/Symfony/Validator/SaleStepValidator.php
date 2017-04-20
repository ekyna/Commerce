<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SaleStepValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleStepValidator implements SaleStepValidatorInterface
{
    protected ValidatorInterface $validator;
    protected ?ConstraintViolationListInterface $violationList = null;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate(SaleInterface $sale, string $step): bool
    {
        $this->validateStep($step);

        $this->violationList = $this->validator->validate(
            $sale,
            $this->getConstraintsForStep($step),
            $this->getGroupsForStep($step)
        );

        return 0 === $this->violationList->count();
    }

    /**
     * @inheritDoc
     */
    public function getViolationList()
    {
        return $this->violationList;
    }

    /**
     * Returns the validation constraints for the given step.
     *
     * @return Constraint[]
     */
    protected function getConstraintsForStep(string $step): array
    {
        $constraints = [new Valid()];

        if ($step === static::SHIPMENT_STEP) {
            $constraints[] = new Constraints\SaleShipmentStep();
        }

        if ($step === static::PAYMENT_STEP) {
            $constraints[] = new Constraints\SaleShipmentStep();
            $constraints[] = new Constraints\RelayPoint();
            $constraints[] = new Constraints\SalePaymentStep();
        }

        return $constraints;
    }

    /**
     * Returns the validation groups for the given step.
     */
    protected function getGroupsForStep(string $step): array
    {
        $groups = ['Default'];

        if ($step === static::CHECKOUT_STEP) {
            $groups[] = 'Checkout';
            $groups[] = 'Identity';
            $groups[] = 'Availability';
        } elseif ($step === static::SHIPMENT_STEP) {
            $groups[] = 'Availability';
        }

        return $groups;
    }

    /**
     * Validates the step.
     */
    protected function validateStep(string $step): void
    {
        if (in_array($step, [self::CHECKOUT_STEP, self::SHIPMENT_STEP, self::PAYMENT_STEP], true)) {
            return;
        }

        throw new InvalidArgumentException('Unexpected step name');
    }
}

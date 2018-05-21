<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SaleStepValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleStepValidator implements SaleStepValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected $violationList;


    /**
     * Constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function validate(SaleInterface $sale, $step)
    {
        $this->validateStep($step);

        $this->violationList = $this->validator->validate(
            $sale,
            $this->getConstraintsForStep($step),
            $this->getGroupsForStep($step)
        );

        return 0 == $this->violationList->count();
    }

    /**
     * @inheritdoc
     */
    public function getViolationList()
    {
        return $this->violationList;
    }

    /**
     * Returns the validation constraints for the given step.
     *
     * @param string $step
     *
     * @return array|\Symfony\Component\Validator\Constraint[]
     */
    protected function getConstraintsForStep($step)
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
     *
     * @param string $step
     *
     * @return array
     */
    protected function getGroupsForStep($step)
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
     *
     * @param string $step
     */
    protected function validateStep($step)
    {
        if (!in_array($step, [static::CHECKOUT_STEP, static::SHIPMENT_STEP, static::PAYMENT_STEP])) {
            throw new InvalidArgumentException("Unexpected step name");
        }
    }
}

<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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
    private $violationList;


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
        $this->violationList = $this->validator->validate($sale, $this->getConstraintsForStep($step));

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
     * Returns the constraints to validate for the given step.
     *
     * @param string $step
     *
     * @return array|\Symfony\Component\Validator\Constraint[]
     */
    protected function getConstraintsForStep($step)
    {
        $this->validateStep($step);

        if ($step === static::SHIPMENT_STEP) {
            return [
                new Constraints\SaleShipmentStep(),
            ];
        }

        if ($step === static::PAYMENT_STEP) {
            return [
                new Constraints\SaleShipmentStep(),
                new Constraints\SalePaymentStep(),
            ];
        }

        return [];
    }

    /**
     * Validates the step.
     *
     * @param string $step
     */
    protected function validateStep($step)
    {
        if (!in_array($step, [static::SHIPMENT_STEP, static::PAYMENT_STEP])) {
            throw new InvalidArgumentException('Invalid step.');
        }
    }
}

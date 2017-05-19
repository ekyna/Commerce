<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class StockUnitValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     */
    public function validate($stockUnit, Constraint $constraint)
    {
        if (null === $stockUnit) {
            return;
        }

        if (!$stockUnit instanceof StockUnitInterface) {
            throw new UnexpectedTypeException($stockUnit, StockUnitInterface::class);
        }
        if (!$constraint instanceof StockUnit) {
            throw new UnexpectedTypeException($constraint, StockUnit::class);
        }

        if ($stockUnit->getReceivedQuantity() > $stockUnit->getOrderedQuantity()) {
            $this->context
                ->buildViolation($constraint->received_must_be_lower_than_ordered)
                ->setInvalidValue($stockUnit->getReceivedQuantity())
                ->atPath('receivedQuantity')
                ->addViolation();
        }
        if ($stockUnit->getShippedQuantity() > $stockUnit->getReceivedQuantity()) {
            $this->context
                ->buildViolation($constraint->shipped_must_be_lower_than_received)
                ->setInvalidValue($stockUnit->getShippedQuantity())
                ->atPath('shippedQuantity')
                ->addViolation();
        }
        if ($stockUnit->getShippedQuantity() > $stockUnit->getSoldQuantity()) {
            $this->context
                ->buildViolation($constraint->shipped_must_be_lower_than_sold)
                ->setInvalidValue($stockUnit->getShippedQuantity())
                ->atPath('shippedQuantity')
                ->addViolation();
        }
    }
}

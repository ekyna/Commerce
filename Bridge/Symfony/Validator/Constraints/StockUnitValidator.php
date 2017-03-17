<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
        /**
         * @var StockUnitInterface $stockUnit
         * @var StockUnit          $constraint
         */
        if ($stockUnit->getDeliveredQuantity() > $stockUnit->getOrderedQuantity()) {
            $this->context
                ->buildViolation($constraint->delivered_must_be_lower_than_ordered)
                ->atPath('deliveredQuantity')
                ->addViolation();
        }
        if ($stockUnit->getShippedQuantity() > $stockUnit->getDeliveredQuantity()) {
            $this->context
                ->buildViolation($constraint->shipped_must_be_lower_than_delivered)
                ->atPath('shippedQuantity')
                ->addViolation();
        }
        if ($stockUnit->getShippedQuantity() > $stockUnit->getReservedQuantity()) {
            $this->context
                ->buildViolation($constraint->delivered_must_be_lower_than_ordered)
                ->atPath('deliveredQuantity')
                ->addViolation();
        }
    }
}

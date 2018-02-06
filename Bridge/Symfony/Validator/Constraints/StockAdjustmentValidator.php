<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class StockAdjustmentValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     */
    public function validate($adjustment, Constraint $constraint)
    {
        if (null === $adjustment) {
            return;
        }

        if (!$adjustment instanceof StockAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, StockAdjustmentInterface::class);
        }
        if (!$constraint instanceof StockAdjustment) {
            throw new UnexpectedTypeException($constraint, StockAdjustment::class);
        }

        if (!StockAdjustmentReasons::isDebitReason($adjustment->getReason())) {
            return;
        }

        $unit = $adjustment->getStockUnit();

        $max = $unit->getReceivedQuantity() - $unit->getShippedQuantity();
        foreach ($unit->getStockAdjustments() as $a) {
            if ($a === $adjustment) {
                continue;
            }

            $max += StockAdjustmentReasons::isDebitReason($a->getReason())
                ? -$a->getQuantity() : $a->getQuantity();
        }

        if ($max < $adjustment->getQuantity()) {
            $this->context
                ->buildViolation($constraint->stock_unit_shipped_quantity_overflow, [
                    '%max%' => $max,
                ])
                ->setInvalidValue($adjustment->getQuantity())
                ->atPath('quantity')
                ->addViolation();
        }
    }
}

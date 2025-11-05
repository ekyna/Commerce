<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
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
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof StockAdjustmentInterface) {
            throw new UnexpectedTypeException($value, StockAdjustmentInterface::class);
        }
        if (!$constraint instanceof StockAdjustment) {
            throw new UnexpectedTypeException($constraint, StockAdjustment::class);
        }

        if (!StockAdjustmentReasons::isDebitReason($value->getReason())) {
            return;
        }

        $unit = $value->getStockUnit();

        $max = $unit->getReceivedQuantity() - $unit->getShippedQuantity();
        foreach ($unit->getStockAdjustments() as $a) {
            if ($a === $value) {
                continue;
            }

            $max += StockAdjustmentReasons::isDebitReason($a->getReason())
                ? -$a->getQuantity() : $a->getQuantity();
        }

        foreach ($unit->getStockAssignments() as $assignment) {
            $assignable = $assignment->getAssignable();
            if (
                $assignable instanceof OrderItemInterface
                && ShipmentStates::STATE_PREPARATION === $assignable->getRootSale()->getShipmentState()
            ) {
                $max -= $assignment->getShippableQuantity();
            }
        }

        if ($max < $value->getQuantity()) {
            $this->context
                ->buildViolation($constraint->stock_unit_shipped_quantity_overflow, [
                    '%max%' => $max->toFixed(3),
                ])
                ->setInvalidValue($value->getQuantity())
                ->atPath('quantity')
                ->addViolation();
        }
    }
}

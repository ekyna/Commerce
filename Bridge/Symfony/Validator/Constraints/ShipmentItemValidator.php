<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Util\ShipmentUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ShipmentItemValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof ShipmentItemInterface) {
            throw new UnexpectedTypeException($item, ShipmentItemInterface::class);
        }
        if (!$constraint instanceof ShipmentItem) {
            throw new UnexpectedTypeException($constraint, ShipmentItem::class);
        }

        $expected = ShipmentUtil::calculateExpectedQuantity($item);
        $available = ShipmentUtil::calculateAvailableQuantity($item);

        if ($available < $expected) {
            // Shipment item's quantity must be lower than or equals the shipment item's available quantity
            if ($item->getQuantity() > $available) {
                $this
                    ->context
                    ->buildViolation($constraint->quantity_must_be_lower_than_or_equal_available, [
                        '%max%' => $available
                    ])
                    ->atPath('quantity')
                    ->addViolation();

                return;
            }
        } else {
            // Shipment item's quantity must be lower than or equals the shipment item's available expected
            if ($item->getQuantity() > $expected) {
                $this
                    ->context
                    ->buildViolation($constraint->quantity_must_be_lower_than_or_equal_expected, [
                        '%max%' => $expected
                    ])
                    ->atPath('quantity')
                    ->addViolation();

                return;
            }
        }
    }
}

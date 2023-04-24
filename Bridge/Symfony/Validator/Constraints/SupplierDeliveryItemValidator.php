<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SupplierDeliveryItemValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof SupplierDeliveryItemInterface) {
            throw new UnexpectedTypeException($value, SupplierDeliveryItemInterface::class);
        }
        if (!$constraint instanceof SupplierDeliveryItem) {
            throw new UnexpectedTypeException($constraint, SupplierDeliveryItem::class);
        }

        // Assert that the delivery item's order item belongs to the delivery's order.
        $found = false;
        $orderItems = $value->getDelivery()->getOrder()->getItems();
        foreach ($orderItems as $orderItem) {
            if ($value->getOrderItem()->getId() == $orderItem->getId()) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this
                ->context
                ->buildViolation($constraint->order_item_miss_match)
                ->addViolation();

            return;
        }

        // Delivery item's quantity must be lower than or equals the order item's remaining delivery quantity
        if ($value->getQuantity() > $max = SupplierUtil::calculateDeliveryRemainingQuantity($value)) {
            $this
                ->context
                ->buildViolation($constraint->quantity_must_be_lower_than_or_equal_ordered, [
                    '%max%' => $max
                ])
                ->atPath('quantity')
                ->addViolation();
        }
    }
}

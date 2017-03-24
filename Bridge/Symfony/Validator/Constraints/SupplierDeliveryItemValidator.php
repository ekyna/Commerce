<?php

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
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof SupplierDeliveryItemInterface) {
            throw new UnexpectedTypeException($item, SupplierDeliveryItemInterface::class);
        }
        if (!$constraint instanceof SupplierDeliveryItem) {
            throw new UnexpectedTypeException($constraint, SupplierDeliveryItem::class);
        }

        // Assert that the delivery item's order item belongs to the delivery's order.
        $found = false;
        $orderItems = $item->getDelivery()->getOrder()->getItems();
        foreach ($orderItems as $orderItem) {
            if ($item->getOrderItem()->getId() == $orderItem->getId()) {
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
        if ($item->getQuantity() > $max = SupplierUtil::calculateDeliveryRemainingQuantity($item)) {
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

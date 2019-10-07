<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SupplierDeliveryValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($delivery, Constraint $constraint)
    {
        if (null === $delivery) {
            return;
        }

        if (!$delivery instanceof SupplierDeliveryInterface) {
            throw new UnexpectedTypeException($delivery, SupplierDeliveryInterface::class);
        }
        if (!$constraint instanceof SupplierDelivery) {
            throw new UnexpectedTypeException($constraint, SupplierDelivery::class);
        }

        if (!SupplierOrderStates::isStockableState($delivery->getOrder()->getState())) {
            $this
                ->context
                ->buildViolation($constraint->unexpected_order_state)
                ->addViolation();

            return;
        }

        // Order item uniqueness
        $orderItemIds = [];
        foreach ($delivery->getItems() as $deliveryItem) {
            if (in_array($id = $deliveryItem->getOrderItem()->getId(), $orderItemIds)) {
                $this
                    ->context
                    ->buildViolation($constraint->duplicate_order_item)
                    ->addViolation();

                return;
            }

            $orderItemIds[] = $id;
        }
    }
}

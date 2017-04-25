<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SupplierOrderValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($order, Constraint $constraint)
    {
        if (null === $order) {
            return;
        }

        if (!$order instanceof SupplierOrderInterface) {
            throw new UnexpectedTypeException($order, SupplierOrderInterface::class);
        }
        if (!$constraint instanceof SupplierOrder) {
            throw new UnexpectedTypeException($constraint, SupplierOrder::class);
        }

        // Subject identity uniqueness
        $identities = [];
        foreach ($order->getItems() as $item) {
            $identity = $item->getSubjectIdentity();
            if ($identity->hasIdentity()) {
                /** @var \Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity $i */
                foreach ($identities as $i) {
                    if ($i->equals($item->getSubjectIdentity())) {
                        $this
                            ->context
                            ->buildViolation($constraint->duplicate_subject)
                            ->atPath('items')
                            ->addViolation();

                        return;
                    }
                }
                $identities[] = $identity;
            }
        }

        // Each deliveries items must match an order item
        foreach ($order->getDeliveries() as $delivery) {
            foreach ($delivery->getItems() as $deliveryItem) {
                foreach ($order->getItems() as $orderItem) {
                    if ($deliveryItem->getOrderItem()->getId() == $orderItem->getId()) {
                        continue 2;
                    }
                }

                $this
                    ->context
                    ->buildViolation($constraint->order_and_delivery_items_miss_match)
                    ->atPath('items')
                    ->addViolation();

                return;
            }
        }
    }
}

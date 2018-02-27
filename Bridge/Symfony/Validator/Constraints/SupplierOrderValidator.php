<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
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

        // Stockable supplier order must have an EDA
        if (SupplierOrderStates::isStockableState($order->getState()) && null === $order->getEstimatedDateOfArrival()) {
            $this
                ->context
                ->buildViolation($constraint->null_estimated_date_of_arrival)
                ->atPath('estimatedDateOfArrival')
                ->addViolation();
        }

        // Supplier products duplication
        $products = [];
        foreach ($order->getItems() as $item) {
            if (null === $product = $item->getProduct()) {
                continue;
            }
            if (in_array ($product, $products, true)) {
                $this
                    ->context
                    ->buildViolation($constraint->duplicate_product)
                    ->atPath('items')
                    ->addViolation();

                return;
            }
            $products[] = $product;
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

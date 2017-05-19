<?php

namespace Ekyna\Component\Commerce\Supplier\Util;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Class SupplierUtil
 * @package Ekyna\Component\Commerce\Supplier\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierUtil
{
    /**
     * Calculate the given supplier order item's received quantity.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return float
     */
    static public function calculateReceivedQuantity(SupplierOrderItemInterface $item)
    {
        $quantity = 0;

        foreach ($item->getOrder()->getDeliveries() as $delivery) {
            foreach ($delivery->getItems() as $deliveryItem) {
                if ($item === $deliveryItem->getOrderItem()) {
                    $quantity += $deliveryItem->getQuantity();
                    continue 2;
                }
            }
        }

        return $quantity;
    }

    /**
     * Calculate the given supplier order item's delivery remaining quantity.
     *
     * @param SupplierOrderItemInterface|SupplierDeliveryItemInterface $item
     *
     * @return float
     */
    static public function calculateDeliveryRemainingQuantity($item)
    {
        if ($item instanceof SupplierOrderItemInterface) {
            return $item->getQuantity() - static::calculateReceivedQuantity($item);
        }

        if (!$item instanceof SupplierDeliveryItemInterface) {
            throw new InvalidArgumentException(
                "Expected instance of " .
                SupplierOrderItemInterface::class . " or " .
                SupplierDeliveryItemInterface::class
            );
        }

        $orderItem = $item->getOrderItem();

        $result = $orderItem->getQuantity() - static::calculateReceivedQuantity($orderItem);

        if (0 < $item->getQuantity()) {
            $result += $item->getQuantity();
        }

        return $result;
    }
}

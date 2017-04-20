<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Util;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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
     */
    public static function calculateReceivedQuantity(SupplierOrderItemInterface $item): Decimal
    {
        $quantity = new Decimal(0);

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
     */
    public static function calculateDeliveryRemainingQuantity($item): Decimal
    {
        if ($item instanceof SupplierOrderItemInterface) {
            return $item->getQuantity()->sub(self::calculateReceivedQuantity($item));
        }

        if (!$item instanceof SupplierDeliveryItemInterface) {
            throw new UnexpectedTypeException($item, [
                SupplierOrderItemInterface::class,
                SupplierDeliveryItemInterface::class,
            ]);
        }

        $orderItem = $item->getOrderItem();

        $result = $orderItem->getQuantity()->sub(self::calculateReceivedQuantity($orderItem));

        if (0 < $item->getQuantity()) {
            $result += $item->getQuantity();
        }

        return $result;
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}

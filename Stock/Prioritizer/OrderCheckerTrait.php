<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Trait OrderCheckerTrait
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait OrderCheckerTrait
{
    /**
     * Checks whether the order can be prioritized.
     */
    protected function checkOrder(OrderInterface $order): bool
    {
        if (!OrderStates::isStockableState($order)) {
            return false;
        }

        if ($order->getShipmentState() === ShipmentStates::STATE_COMPLETED) {
            return false;
        }

        return true;
    }
}

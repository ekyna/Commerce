<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderItemStockAssignmentEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderItemStockAssignmentEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.order_item_stock_assignment.insert';
    const UPDATE         = 'ekyna_commerce.order_item_stock_assignment.update';
    const DELETE         = 'ekyna_commerce.order_item_stock_assignment.delete';
}

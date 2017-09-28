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
    const INSERT      = 'ekyna_commerce.order_item_stock_assignment.insert';
    const UPDATE      = 'ekyna_commerce.order_item_stock_assignment.update';
    const DELETE      = 'ekyna_commerce.order_item_stock_assignment.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.order_item_stock_assignment.initialize';

    const PRE_CREATE  = 'ekyna_commerce.order_item_stock_assignment.pre_create';
    const POST_CREATE = 'ekyna_commerce.order_item_stock_assignment.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.order_item_stock_assignment.pre_update';
    const POST_UPDATE = 'ekyna_commerce.order_item_stock_assignment.post_update';

    const PRE_DELETE  = 'ekyna_commerce.order_item_stock_assignment.pre_delete';
    const POST_DELETE = 'ekyna_commerce.order_item_stock_assignment.post_delete';
}

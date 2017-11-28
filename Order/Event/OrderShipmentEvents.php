<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderShipmentEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderShipmentEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.order_shipment.insert';
    const UPDATE         = 'ekyna_commerce.order_shipment.update';
    const DELETE         = 'ekyna_commerce.order_shipment.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.order_shipment.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.order_shipment.content_change';

    const INITIALIZE     = 'ekyna_commerce.order_shipment.initialize';

    const PRE_CREATE     = 'ekyna_commerce.order_shipment.pre_create';
    const POST_CREATE    = 'ekyna_commerce.order_shipment.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.order_shipment.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.order_shipment.post_update';

    const PRE_DELETE     = 'ekyna_commerce.order_shipment.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.order_shipment.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

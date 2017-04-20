<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderShipmentEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderShipmentEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.order_shipment.insert';
    public const UPDATE         = 'ekyna_commerce.order_shipment.update';
    public const DELETE         = 'ekyna_commerce.order_shipment.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.order_shipment.state_change';
    public const CONTENT_CHANGE = 'ekyna_commerce.order_shipment.content_change';

    public const PRE_CREATE     = 'ekyna_commerce.order_shipment.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.order_shipment.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.order_shipment.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.order_shipment.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.order_shipment.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.order_shipment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

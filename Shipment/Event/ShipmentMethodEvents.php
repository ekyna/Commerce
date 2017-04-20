<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Event;

/**
 * Class ShipmentMethodEvents
 * @package Ekyna\Component\Commerce\Shipment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ShipmentMethodEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.shipment_method.insert';
    public const UPDATE      = 'ekyna_commerce.shipment_method.update';
    public const DELETE      = 'ekyna_commerce.shipment_method.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.shipment_method.pre_create';
    public const POST_CREATE = 'ekyna_commerce.shipment_method.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.shipment_method.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.shipment_method.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.shipment_method.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.shipment_method.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

namespace Ekyna\Component\Commerce\Shipment\Event;

/**
 * Class ShipmentMethodEvents
 * @package Ekyna\Component\Commerce\Shipment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ShipmentMethodEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.shipment_method.insert';
    const UPDATE      = 'ekyna_commerce.shipment_method.update';
    const DELETE      = 'ekyna_commerce.shipment_method.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.shipment_method.initialize';

    const PRE_CREATE  = 'ekyna_commerce.shipment_method.pre_create';
    const POST_CREATE = 'ekyna_commerce.shipment_method.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.shipment_method.pre_update';
    const POST_UPDATE = 'ekyna_commerce.shipment_method.post_update';

    const PRE_DELETE  = 'ekyna_commerce.shipment_method.pre_delete';
    const POST_DELETE = 'ekyna_commerce.shipment_method.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

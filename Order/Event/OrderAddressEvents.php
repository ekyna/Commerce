<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderAddressEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderAddressEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.order_address.insert';
    const UPDATE      = 'ekyna_commerce.order_address.update';
    const DELETE      = 'ekyna_commerce.order_address.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.order_address.initialize';

    const PRE_CREATE  = 'ekyna_commerce.order_address.pre_create';
    const POST_CREATE = 'ekyna_commerce.order_address.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.order_address.pre_update';
    const POST_UPDATE = 'ekyna_commerce.order_address.post_update';

    const PRE_DELETE  = 'ekyna_commerce.order_address.pre_delete';
    const POST_DELETE = 'ekyna_commerce.order_address.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

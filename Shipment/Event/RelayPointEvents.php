<?php

namespace Ekyna\Component\Commerce\Shipment\Event;

/**
 * Class RelayPointEvents
 * @package Ekyna\Component\Commerce\Shipment\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPointEvents
{
    // Persistence
    const INSERT = 'ekyna_commerce.relay_point.insert';
    const UPDATE = 'ekyna_commerce.relay_point.update';
    const DELETE = 'ekyna_commerce.relay_point.delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

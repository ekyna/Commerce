<?php

namespace Ekyna\Component\Commerce\Common\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class NotificationEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationEvent extends Event
{
    private $sale;

    private $child; // Or "subResource" or "origin" (payment, shipment, invoice, etc)

    private $type; // Or "reason" or "trigger"
}
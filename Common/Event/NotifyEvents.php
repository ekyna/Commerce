<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

/**
 * Class NotifyEvents
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class NotifyEvents
{
    public const BUILD = 'ekyna_commerce.notify.build';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

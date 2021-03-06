<?php

namespace Ekyna\Component\Commerce\Common\Event;

/**
 * Class ContextEvents
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ContextEvents
{
    const BUILD  = 'ekyna_commerce.context.build';
    const CHANGE = 'ekyna_commerce.context.change';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

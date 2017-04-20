<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

/**
 * Class ContextEvents
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ContextEvents
{
    public const BUILD  = 'ekyna_commerce.context.build';
    public const CHANGE = 'ekyna_commerce.context.change';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

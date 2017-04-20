<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

/**
 * Class SaleItemEvents
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SaleItemEvents
{
    public const INITIALIZE = 'ekyna_commerce.sale_item.initialize';
    public const BUILD      = 'ekyna_commerce.sale_item.build';
    public const DISCOUNT   = 'ekyna_commerce.sale_item.discount';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

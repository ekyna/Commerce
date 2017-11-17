<?php

namespace Ekyna\Component\Commerce\Common\Event;

/**
 * Class SaleItemEvents
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SaleItemEvents
{
    const INITIALIZE = 'ekyna_commerce.sale_item.initialize';
    const BUILD      = 'ekyna_commerce.sale_item.build';
    const DISCOUNT   = 'ekyna_commerce.sale_item.discount';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

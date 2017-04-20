<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Event;

/**
 * Class StockUnitEvents
 * @package Ekyna\Component\Commerce\Stock\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class StockUnitEvents
{
    public const COST_CHANGE = 'ekyna_commerce.stock_unit.cost_change';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

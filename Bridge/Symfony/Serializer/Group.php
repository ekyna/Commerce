<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer;

/**
 * Class Group
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class Group
{
    public const STOCK_VIEW       = 'StockView';
    public const STOCK_UNIT       = 'StockUnit';
    public const STOCK_ASSIGNMENT = 'StockAssignment';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

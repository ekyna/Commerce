<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Event;

/**
 * Class StockAdjustmentEvents
 * @package Ekyna\Component\Commerce\Stock\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockAdjustmentEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.stock_adjustment.insert';
    public const UPDATE      = 'ekyna_commerce.stock_adjustment.update';
    public const DELETE      = 'ekyna_commerce.stock_adjustment.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.stock_adjustment.pre_create';
    public const POST_CREATE = 'ekyna_commerce.stock_adjustment.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.stock_adjustment.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.stock_adjustment.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.stock_adjustment.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.stock_adjustment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

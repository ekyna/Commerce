<?php

namespace Ekyna\Component\Commerce\Stock\Event;

/**
 * Class StockAdjustmentEvents
 * @package Ekyna\Component\Commerce\Stock\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockAdjustmentEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.stock_adjustment.insert';
    const UPDATE      = 'ekyna_commerce.stock_adjustment.update';
    const DELETE      = 'ekyna_commerce.stock_adjustment.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.stock_adjustment.initialize';

    const PRE_CREATE  = 'ekyna_commerce.stock_adjustment.pre_create';
    const POST_CREATE = 'ekyna_commerce.stock_adjustment.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.stock_adjustment.pre_update';
    const POST_UPDATE = 'ekyna_commerce.stock_adjustment.post_update';

    const PRE_DELETE  = 'ekyna_commerce.stock_adjustment.pre_delete';
    const POST_DELETE = 'ekyna_commerce.stock_adjustment.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
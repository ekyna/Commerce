<?php

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuoteItemAdjustmentEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteItemAdjustmentEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.quote_item_adjustment.insert';
    const UPDATE      = 'ekyna_commerce.quote_item_adjustment.update';
    const DELETE      = 'ekyna_commerce.quote_item_adjustment.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.quote_item_adjustment.initialize';

    const PRE_CREATE  = 'ekyna_commerce.quote_item_adjustment.pre_create';
    const POST_CREATE = 'ekyna_commerce.quote_item_adjustment.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.quote_item_adjustment.pre_update';
    const POST_UPDATE = 'ekyna_commerce.quote_item_adjustment.post_update';

    const PRE_DELETE  = 'ekyna_commerce.quote_item_adjustment.pre_delete';
    const POST_DELETE = 'ekyna_commerce.quote_item_adjustment.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

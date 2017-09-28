<?php

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuoteAdjustmentEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteAdjustmentEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.quote_adjustment.insert';
    const UPDATE      = 'ekyna_commerce.quote_adjustment.update';
    const DELETE      = 'ekyna_commerce.quote_adjustment.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.quote_adjustment.initialize';

    const PRE_CREATE  = 'ekyna_commerce.quote_adjustment.pre_create';
    const POST_CREATE = 'ekyna_commerce.quote_adjustment.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.quote_adjustment.pre_update';
    const POST_UPDATE = 'ekyna_commerce.quote_adjustment.post_update';

    const PRE_DELETE  = 'ekyna_commerce.quote_adjustment.pre_delete';
    const POST_DELETE = 'ekyna_commerce.quote_adjustment.post_delete';
}

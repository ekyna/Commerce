<?php

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuoteItemEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteItemEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.quote_item.insert';
    const UPDATE         = 'ekyna_commerce.quote_item.update';
    const DELETE         = 'ekyna_commerce.quote_item.delete';

    // Domain
    const PRE_CREATE     = 'ekyna_commerce.quote_item.pre_create';
    const POST_CREATE    = 'ekyna_commerce.quote_item.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.quote_item.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.quote_item.post_update';

    const PRE_DELETE     = 'ekyna_commerce.quote_item.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.quote_item.post_delete';
}

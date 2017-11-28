<?php

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuoteEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.quote.insert';
    const UPDATE         = 'ekyna_commerce.quote.update';
    const DELETE         = 'ekyna_commerce.quote.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.quote.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.quote.content_change';
    const ADDRESS_CHANGE = 'ekyna_commerce.quote.address_change';

    const INITIALIZE     = 'ekyna_commerce.quote.initialize';

    const PRE_CREATE     = 'ekyna_commerce.quote.pre_create';
    const POST_CREATE    = 'ekyna_commerce.quote.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.quote.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.quote.post_update';

    const PRE_DELETE     = 'ekyna_commerce.quote.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.quote.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

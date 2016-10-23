<?php

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuoteAddressEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteAddressEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.quote_address.insert';
    const UPDATE         = 'ekyna_commerce.quote_address.update';
    const DELETE         = 'ekyna_commerce.quote_address.delete';

    // Domain
    const PRE_CREATE     = 'ekyna_commerce.quote_address.pre_create';
    const POST_CREATE    = 'ekyna_commerce.quote_address.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.quote_address.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.quote_address.post_update';

    const PRE_DELETE     = 'ekyna_commerce.quote_address.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.quote_address.post_delete';
}

<?php

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuotePaymentEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuotePaymentEvents
{
    // Persistence
    const INSERT = 'ekyna_commerce.quote_payment.insert';
    const UPDATE = 'ekyna_commerce.quote_payment.update';
    const DELETE = 'ekyna_commerce.quote_payment.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.quote_payment.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.quote_payment.content_change';

    const INITIALIZE     = 'ekyna_commerce.quote_payment.initialize';

    const PRE_CREATE     = 'ekyna_commerce.quote_payment.pre_create';
    const POST_CREATE    = 'ekyna_commerce.quote_payment.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.quote_payment.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.quote_payment.post_update';

    const PRE_DELETE     = 'ekyna_commerce.quote_payment.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.quote_payment.post_delete';
}

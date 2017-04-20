<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuotePaymentEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuotePaymentEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.quote_payment.insert';
    public const UPDATE         = 'ekyna_commerce.quote_payment.update';
    public const DELETE         = 'ekyna_commerce.quote_payment.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.quote_payment.state_change';
    public const CONTENT_CHANGE = 'ekyna_commerce.quote_payment.content_change';

    public const PRE_CREATE     = 'ekyna_commerce.quote_payment.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.quote_payment.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.quote_payment.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.quote_payment.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.quote_payment.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.quote_payment.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuoteEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.quote.insert';
    public const UPDATE         = 'ekyna_commerce.quote.update';
    public const DELETE         = 'ekyna_commerce.quote.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.quote.state_change';
    public const CONTENT_CHANGE = 'ekyna_commerce.quote.content_change';
    public const ADDRESS_CHANGE = 'ekyna_commerce.quote.address_change';

    public const PRE_CREATE     = 'ekyna_commerce.quote.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.quote.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.quote.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.quote.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.quote.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.quote.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuoteItemEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteItemEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.quote_item.insert';
    public const UPDATE      = 'ekyna_commerce.quote_item.update';
    public const DELETE      = 'ekyna_commerce.quote_item.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.quote_item.pre_create';
    public const POST_CREATE = 'ekyna_commerce.quote_item.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.quote_item.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.quote_item.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.quote_item.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.quote_item.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

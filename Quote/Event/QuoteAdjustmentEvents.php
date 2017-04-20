<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Event;

/**
 * Class QuoteAdjustmentEvents
 * @package Ekyna\Component\Commerce\Quote\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuoteAdjustmentEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.quote_adjustment.insert';
    public const UPDATE      = 'ekyna_commerce.quote_adjustment.update';
    public const DELETE      = 'ekyna_commerce.quote_adjustment.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.quote_adjustment.pre_create';
    public const POST_CREATE = 'ekyna_commerce.quote_adjustment.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.quote_adjustment.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.quote_adjustment.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.quote_adjustment.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.quote_adjustment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

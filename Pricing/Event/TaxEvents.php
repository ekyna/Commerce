<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Event;

/**
 * Class TaxEvents
 * @package Ekyna\Component\Commerce\Pricing\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TaxEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.tax.insert';
    public const UPDATE      = 'ekyna_commerce.tax.update';
    public const DELETE      = 'ekyna_commerce.tax.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.tax.pre_create';
    public const POST_CREATE = 'ekyna_commerce.tax.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.tax.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.tax.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.tax.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.tax.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

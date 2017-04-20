<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Event;

/**
 * Class TaxGroupEvents
 * @package Ekyna\Component\Commerce\Pricing\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TaxGroupEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.tax_group.insert';
    public const UPDATE      = 'ekyna_commerce.tax_group.update';
    public const DELETE      = 'ekyna_commerce.tax_group.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.tax_group.pre_create';
    public const POST_CREATE = 'ekyna_commerce.tax_group.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.tax_group.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.tax_group.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.tax_group.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.tax_group.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

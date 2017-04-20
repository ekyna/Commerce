<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Event;

/**
 * Class TaxRuleEvents
 * @package Ekyna\Component\Commerce\Pricing\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TaxRuleEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.tax_rule.insert';
    public const UPDATE      = 'ekyna_commerce.tax_rule.update';
    public const DELETE      = 'ekyna_commerce.tax_rule.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.tax_rule.pre_create';
    public const POST_CREATE = 'ekyna_commerce.tax_rule.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.tax_rule.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.tax_rule.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.tax_rule.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.tax_rule.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

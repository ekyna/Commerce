<?php

namespace Ekyna\Component\Commerce\Pricing\Event;

/**
 * Class TaxRuleEvents
 * @package Ekyna\Component\Commerce\Pricing\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TaxRuleEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.tax_rule.insert';
    const UPDATE      = 'ekyna_commerce.tax_rule.update';
    const DELETE      = 'ekyna_commerce.tax_rule.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.tax_rule.initialize';

    const PRE_CREATE  = 'ekyna_commerce.tax_rule.pre_create';
    const POST_CREATE = 'ekyna_commerce.tax_rule.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.tax_rule.pre_update';
    const POST_UPDATE = 'ekyna_commerce.tax_rule.post_update';

    const PRE_DELETE  = 'ekyna_commerce.tax_rule.pre_delete';
    const POST_DELETE = 'ekyna_commerce.tax_rule.post_delete';
}

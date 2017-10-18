<?php

namespace Ekyna\Component\Commerce\Pricing\Event;

/**
 * Class TaxGroupEvents
 * @package Ekyna\Component\Commerce\Pricing\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TaxGroupEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.tax_group.insert';
    const UPDATE      = 'ekyna_commerce.tax_group.update';
    const DELETE      = 'ekyna_commerce.tax_group.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.tax_group.initialize';

    const PRE_CREATE  = 'ekyna_commerce.tax_group.pre_create';
    const POST_CREATE = 'ekyna_commerce.tax_group.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.tax_group.pre_update';
    const POST_UPDATE = 'ekyna_commerce.tax_group.post_update';

    const PRE_DELETE  = 'ekyna_commerce.tax_group.pre_delete';
    const POST_DELETE = 'ekyna_commerce.tax_group.post_delete';
}

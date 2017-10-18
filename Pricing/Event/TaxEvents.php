<?php

namespace Ekyna\Component\Commerce\Pricing\Event;

/**
 * Class TaxEvents
 * @package Ekyna\Component\Commerce\Pricing\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TaxEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.tax.insert';
    const UPDATE      = 'ekyna_commerce.tax.update';
    const DELETE      = 'ekyna_commerce.tax.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.tax.initialize';

    const PRE_CREATE  = 'ekyna_commerce.tax.pre_create';
    const POST_CREATE = 'ekyna_commerce.tax.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.tax.pre_update';
    const POST_UPDATE = 'ekyna_commerce.tax.post_update';

    const PRE_DELETE  = 'ekyna_commerce.tax.pre_delete';
    const POST_DELETE = 'ekyna_commerce.tax.post_delete';
}

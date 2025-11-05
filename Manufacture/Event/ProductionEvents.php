<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Event;

/**
 * Class ProductionEvents
 * @package Ekyna\Component\Commerce\Manufacture\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.production.insert';
    public const UPDATE         = 'ekyna_commerce.production.update';
    public const DELETE         = 'ekyna_commerce.production.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.production.state_change';

    public const PRE_CREATE     = 'ekyna_commerce.production.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.production.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.production.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.production.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.production.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.production.post_delete';
}

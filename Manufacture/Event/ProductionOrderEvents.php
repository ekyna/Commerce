<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Event;

/**
 * Class ProductionOrderEvents
 * @package Ekyna\Component\Commerce\Manufacture\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.production_order.insert';
    public const UPDATE         = 'ekyna_commerce.production_order.update';
    public const DELETE         = 'ekyna_commerce.production_order.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.production_order.state_change';

    public const PRE_CREATE     = 'ekyna_commerce.production_order.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.production_order.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.production_order.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.production_order.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.production_order.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.production_order.post_delete';
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Event;

/**
 * Class ProductionItemEvents
 * @package Ekyna\Component\Commerce\Manufacture\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionItemEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.production_item.insert';
    public const UPDATE         = 'ekyna_commerce.production_item.update';
    public const DELETE         = 'ekyna_commerce.production_item.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.production_item.state_change';

    public const PRE_CREATE     = 'ekyna_commerce.production_item.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.production_item.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.production_item.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.production_item.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.production_item.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.production_item.post_delete';
}

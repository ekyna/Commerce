<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Event;

/**
 * Class BillOfMaterialsEvents
 * @package Ekyna\Component\Commerce\Manufacture\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.bill_of_materials.insert';
    public const UPDATE         = 'ekyna_commerce.bill_of_materials.update';
    public const DELETE         = 'ekyna_commerce.bill_of_materials.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.bill_of_materials.state_change';

    public const PRE_CREATE     = 'ekyna_commerce.bill_of_materials.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.bill_of_materials.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.bill_of_materials.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.bill_of_materials.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.bill_of_materials.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.bill_of_materials.post_delete';
}

<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ShipmentItem
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItem extends Constraint
{
    public $returnable_overflow       = 'ekyna_commerce.shipment_item.returnable_overflow';
    public $shippable_overflow        = 'ekyna_commerce.shipment_item.shippable_overflow';
    public $parent_quantity_integrity = 'ekyna_commerce.shipment_item.parent_quantity_integrity';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

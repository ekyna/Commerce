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
    public $quantity_must_be_lower_than_or_equal_available = 'ekyna_commerce.shipment_item.quantity_must_be_lower_than_or_equal_available';
    public $quantity_must_be_lower_than_or_equal_expected = 'ekyna_commerce.shipment_item.quantity_must_be_lower_than_or_equal_expected';
    public $quantity_must_be_lower_than_or_equal_shipped = 'ekyna_commerce.shipment_item.quantity_must_be_lower_than_or_equal_shipped';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SupplierDeliveryItem
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItem extends Constraint
{
    public $quantity_must_be_lower_or_equal_than_ordered = 'ekyna_commerce.supplier_delivery_item.quantity_must_be_lower_or_equal_than_ordered';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

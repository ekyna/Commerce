<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SupplierDelivery
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDelivery extends Constraint
{
    public $unexpected_order_state = 'ekyna_commerce.supplier_delivery.unexpected_order_state';
    public $duplicate_order_item = 'ekyna_commerce.supplier_delivery.duplicate_order_item';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

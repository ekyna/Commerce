<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SupplierOrder
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrder extends Constraint
{
    public $order_and_delivery_items_miss_match = 'ekyna_commerce.supplier_order.order_and_delivery_items_miss_match';
    public $payment_total_is_too_low            = 'ekyna_commerce.supplier_order.payment_total_is_too_low';
    public $duplicate_subject                   = 'ekyna_commerce.supplier_order.duplicate_subject';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

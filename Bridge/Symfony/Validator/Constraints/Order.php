<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Order
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Order extends Constraint
{
    public $sample_with_payments_or_invoices = 'ekyna_commerce.order.sample_with_payments_or_invoices';
    public $unexpected_origin_customer       = 'ekyna_commerce.order.unexpected_origin_customer'; // TODO REmove
    public $customers_integrity              = 'ekyna_commerce.order.customers_integrity'; // TODO REmove

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

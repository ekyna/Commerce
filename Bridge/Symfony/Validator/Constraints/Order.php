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
    public $customer_has_parent              = 'ekyna_commerce.order.customer_has_parent';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

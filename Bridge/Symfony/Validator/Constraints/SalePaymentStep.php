<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SalePaymentStep
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SalePaymentStep extends Constraint
{
    public $shipment_method_must_be_set = 'ekyna_commerce.sale.shipment_method_must_be_set';

    /**
     * @inheritdoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

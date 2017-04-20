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
    public $voucher_must_be_set         = 'ekyna_commerce.sale.voucher_must_be_set';
    public $shipment_method_must_be_set = 'ekyna_commerce.sale.shipment_method_must_be_set';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

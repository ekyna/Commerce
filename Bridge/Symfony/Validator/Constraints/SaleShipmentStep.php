<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SaleShipmentStep
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleShipmentStep extends Constraint
{
    public $cart_is_locked               = 'ekyna_commerce.sale.cart_is_locked';
    public $identity_must_be_set         = 'ekyna_commerce.sale.identity_must_be_set';
    public $invoice_address_must_be_set  = 'ekyna_commerce.sale.invoice_address_must_be_set';
    public $delivery_address_must_be_set = 'ekyna_commerce.sale.delivery_address_must_be_set';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class CreditItem
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreditItem extends Constraint
{
    public $shipment_is_not_return = 'ekyna_commerce.credit_item.shipment_is_not_return';
    public $sale_item_and_shipment_item_miss_match = 'ekyna_commerce.credit_item.sale_item_and_shipment_item_miss_match';
    public $quantity_is_greater_than_returned = 'ekyna_commerce.credit_item.quantity_is_greater_than_returned';
    public $sale_and_credit_miss_match = 'ekyna_commerce.credit_item.sale_and_credit_miss_match';
    public $quantity_is_greater_than_creditable = 'ekyna_commerce.credit_item.quantity_is_greater_than_creditable';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

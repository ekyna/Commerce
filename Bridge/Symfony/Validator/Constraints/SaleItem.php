<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SaleItem
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItem extends Constraint
{
    public $tax_group_must_not_be_null      = 'ekyna_commerce.sale_item.tax_group_must_not_be_null';
    public $quantity_is_lower_than_shipped  = 'ekyna_commerce.sale_item.quantity_is_lower_than_shipped';
    public $quantity_is_lower_than_credited = 'ekyna_commerce.sale_item.quantity_is_lower_than_credited';


    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

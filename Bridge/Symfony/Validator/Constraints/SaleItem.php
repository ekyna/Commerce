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
    public $root_item_cant_be_private       = 'ekyna_commerce.sale_item.root_item_cant_be_private';
    public $privacy_integrity               = 'ekyna_commerce.sale_item.privacy_integrity';
    public $tax_group_integrity             = 'ekyna_commerce.sale_item.tax_group_integrity';
    public $quantity_is_lower_than_shipped  = 'ekyna_commerce.sale_item.quantity_is_lower_than_shipped';
    public $quantity_is_lower_than_invoiced = 'ekyna_commerce.sale_item.quantity_is_lower_than_invoiced';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

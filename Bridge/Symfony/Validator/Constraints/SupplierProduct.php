<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SupplierProduct
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProduct extends Constraint
{
    public $duplicate_by_subject = 'ekyna_commerce.supplier_product.duplicate_by_subject';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

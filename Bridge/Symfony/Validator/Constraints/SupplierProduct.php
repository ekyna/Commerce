<?php

declare(strict_types=1);

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
    public $bom_without_subject  = 'ekyna_commerce.supplier_product.bom_without_subject';
    public $bom_with_packing     = 'ekyna_commerce.supplier_product.bom_with_packing';
    public $recursive_bom        = 'ekyna_commerce.supplier_product.recursive_bom';


    /**
     * @inheritDoc
     */
    public function getTargets(): array|string
    {
        return static::CLASS_CONSTRAINT;
    }
}

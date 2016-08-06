<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Customer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer extends Constraint
{
    public $hierarchyOverflow = 'ekyna_commerce.customer.hierarchy_overflow';
    public $parentCompanyIsMandatory = 'ekyna_commerce.customer.parent_company_is_mandatory';
    public $companyIsMandatory = 'ekyna_commerce.customer.company_is_mandatory';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

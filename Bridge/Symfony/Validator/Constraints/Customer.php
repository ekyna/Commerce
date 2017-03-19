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
    public $hierarchy_overflow          = 'ekyna_commerce.customer.hierarchy_overflow';
    public $parent_company_is_mandatory = 'ekyna_commerce.customer.parent_company_is_mandatory';
    public $company_is_mandatory        = 'ekyna_commerce.customer.company_is_mandatory';


    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

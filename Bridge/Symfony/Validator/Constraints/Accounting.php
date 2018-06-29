<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Accounting
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Accounting extends Constraint
{
    public $tax_is_required  = 'ekyna_commerce.accounting.tax_is_required';
    public $tax_must_be_null = 'ekyna_commerce.accounting.tax_must_be_null';
    public $rule_is_required  = 'ekyna_commerce.accounting.rule_is_required';
    public $rule_must_be_null = 'ekyna_commerce.accounting.rule_must_be_null';


    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

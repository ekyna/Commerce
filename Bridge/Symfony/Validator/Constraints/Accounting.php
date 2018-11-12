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
    public $tax_is_required               = 'ekyna_commerce.accounting.tax_is_required';
    public $tax_must_be_null              = 'ekyna_commerce.accounting.tax_must_be_null';
    public $tax_rule_is_required          = 'ekyna_commerce.accounting.tax_rule_is_required';
    public $tax_rule_must_be_null         = 'ekyna_commerce.accounting.tax_rule_must_be_null';
    public $payment_method_is_required    = 'ekyna_commerce.accounting.payment_method_is_required';
    public $payment_method_must_be_null   = 'ekyna_commerce.accounting.payment_method_must_be_null';
    public $customer_groups_is_required   = 'ekyna_commerce.accounting.customer_groups_is_required';
    public $customer_groups_must_be_empty = 'ekyna_commerce.accounting.customer_groups_must_be_empty';


    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

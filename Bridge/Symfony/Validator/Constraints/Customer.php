<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Customer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer extends Constraint
{
    public string $hierarchy_overflow                  = 'ekyna_commerce.customer.hierarchy_overflow';
    public string $non_zero_balance                    = 'ekyna_commerce.customer.non_zero_balance';
    public string $parent_company_is_mandatory         = 'ekyna_commerce.customer.parent_company_is_mandatory';
    public string $company_is_mandatory                = 'ekyna_commerce.customer.company_is_mandatory';
    public string $term_required_for_outstanding       = 'ekyna_commerce.customer.term_required_for_outstanding';
    public string $outstanding_required_for_term       = 'ekyna_commerce.customer.outstanding_required_for_term';
    public string $default_payment_method_must_be_null = 'ekyna_commerce.customer.default_payment_method_must_be_null';
    public string $payment_methods_must_be_empty       = 'ekyna_commerce.customer.payment_methods_must_be_empty';
    public string $default_payment_method_is_mandatory = 'ekyna_commerce.customer.default_payment_method_is_mandatory';
    public string $term_required_for_factor_method     = 'ekyna_commerce.customer.term_required_for_factor_method';
    public string $duplicate_payment_method            = 'ekyna_commerce.customer.duplicate_payment_method';
    public string $unexpected_payment_method           = 'ekyna_commerce.customer.unexpected_payment_method';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Sale
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Sale extends Constraint
{
    public string $customer_group_is_required_if_no_customer = 'ekyna_commerce.sale.no_customer.customer_group_is_required';
    public string $email_is_required_if_no_customer          = 'ekyna_commerce.sale.no_customer.email_is_required';
    public string $delivery_address_is_required              = 'ekyna_commerce.sale.delivery_address.is_required';
    public string $delivery_address_should_be_null           = 'ekyna_commerce.sale.delivery_address.should_be_null';
    public string $shipment_method_require_mobile            = 'ekyna_commerce.sale.shipment_method_require_mobile';
    public string $incoterm_is_required                      = 'ekyna_commerce.sale.incoterm_is_required';
    public string $outstanding_overflow_is_forbidden         = 'ekyna_commerce.sale.outstanding_overflow_is_forbidden';
    public string $outstanding_limit_require_term            = 'ekyna_commerce.sale.outstanding_limit_require_term';
    public string $deposit_greater_than_grand_total          = 'ekyna_commerce.sale.deposit_greater_than_grand_total';
    public string $term_required_for_factor_method           = 'ekyna_commerce.sale.term_required_for_factor_method';


    /**
     * @inheritDoc
     */
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}

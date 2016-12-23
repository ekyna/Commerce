<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Order
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Sale extends Constraint
{
    public $customer_group_is_required_if_no_customer = 'ekyna_commerce.sale.no_customer.customer_group_is_required';
    public $email_is_required_if_no_customer = 'ekyna_commerce.sale.no_customer.email_is_required';
    public $identity_is_required_if_no_customer = 'ekyna_commerce.sale.no_customer.identity_is_required';
    public $delivery_address_is_required = 'ekyna_commerce.sale.delivery_address.is_required';
    public $delivery_address_should_be_null = 'ekyna_commerce.sale.delivery_address.should_be_null';


    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

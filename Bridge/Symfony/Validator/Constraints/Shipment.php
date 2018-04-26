<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Shipment
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Shipment extends Constraint
{
    public $method_does_not_support_parcel       = 'ekyna_commerce.shipment.method_does_not_support_parcel';
    public $method_does_not_support_return       = 'ekyna_commerce.shipment.method_does_not_support_return';
    public $method_does_not_support_shipment     = 'ekyna_commerce.shipment.method_does_not_support_shipment';
    public $method_requires_mobile               = 'ekyna_commerce.shipment.method_requires_mobile';
    public $shipped_state_denied                 = 'ekyna_commerce.shipment.shipped_state_denied';
    public $weight_or_parcels_but_not_both       = 'ekyna_commerce.shipment.weight_or_parcels_but_not_both';
    public $max_weight                           = 'ekyna_commerce.shipment_max_weight';
    public $valorization_or_parcels_but_not_both = 'ekyna_commerce.shipment.valorization_or_parcels_but_not_both';
    public $at_least_two_parcels_or_none         = 'ekyna_commerce.shipment.at_least_two_parcels_or_none';
    public $relay_point_is_required              = 'ekyna_commerce.shipment.relay_point_is_required';
    public $credit_method_is_required            = 'ekyna_commerce.shipment.credit_method_is_required';
    public $credit_method_must_be_null           = 'ekyna_commerce.shipment.credit_method_must_be_null';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

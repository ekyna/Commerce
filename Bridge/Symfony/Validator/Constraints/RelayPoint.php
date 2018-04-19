<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class RelayPoint
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPoint extends Constraint
{
    public $relay_point_is_required  = 'ekyna_commerce.relay_point.is_required';
    public $relay_point_must_be_null = 'ekyna_commerce.relay_point.must_be_null';
    public $gateway_miss_match       = 'ekyna_commerce.relay_point.gateway_miss_match';

    public $relayPointPath          = 'relayPoint';
    public $shipmentMethodPath      = 'shipmentMethod';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

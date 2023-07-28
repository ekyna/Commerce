<?php
/** @noinspection PhpPropertyNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Shipment
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Shipment extends Constraint
{
    public string $method_is_virtual_shipment_is_physical = 'ekyna_commerce.shipment.method_is_virtual_shipment_is_physical';
    public string $method_does_not_support_parcel         = 'ekyna_commerce.shipment.method_does_not_support_parcel';
    public string $method_does_not_support_return         = 'ekyna_commerce.shipment.method_does_not_support_return';
    public string $method_does_not_support_shipment       = 'ekyna_commerce.shipment.method_does_not_support_shipment';
    public string $method_requires_mobile                 = 'ekyna_commerce.shipment.method_requires_mobile';
    public string $shipped_state_denied                   = 'ekyna_commerce.shipment.shipped_state_denied';
    public string $weight_or_parcels_but_not_both         = 'ekyna_commerce.shipment.weight_or_parcels_but_not_both';
    public string $max_weight                             = 'ekyna_commerce.shipment_max_weight';
    public string $valorization_or_parcels_but_not_both   = 'ekyna_commerce.shipment.valorization_or_parcels_but_not_both';
    public string $at_least_two_parcels_or_none           = 'ekyna_commerce.shipment.at_least_two_parcels_or_none';


    /**
     * @inheritDoc
     */
    public function getTargets(): array|string
    {
        return static::CLASS_CONSTRAINT;
    }
}

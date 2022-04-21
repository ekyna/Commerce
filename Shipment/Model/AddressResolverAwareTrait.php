<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;

/**
 * Trait AddressResolverAwareTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait AddressResolverAwareTrait
{
    protected ShipmentAddressResolverInterface $addressResolver;

    public function setAddressResolver(ShipmentAddressResolverInterface $resolver): void
    {
        $this->addressResolver = $resolver;
    }

    public function getAddressResolver(): ShipmentAddressResolverInterface
    {
        return $this->addressResolver;
    }
}

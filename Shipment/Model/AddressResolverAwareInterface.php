<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;

/**
 * Interface AddressResolverAwareInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AddressResolverAwareInterface
{
    /**
     * Sets the shipment address resolver.
     *
     * @param ShipmentAddressResolverInterface $resolver
     */
    public function setAddressResolver(ShipmentAddressResolverInterface $resolver);

    /**
     * Returns the addressResolver.
     *
     * @return ShipmentAddressResolverInterface
     */
    public function getAddressResolver();
}

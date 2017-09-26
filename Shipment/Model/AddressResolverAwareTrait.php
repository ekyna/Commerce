<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;

/**
 * Trait AddressResolverAwareTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait AddressResolverAwareTrait
{
    /**
     * @var ShipmentAddressResolverInterface
     */
    protected $addressResolver;


    /**
     * Sets the shipment address resolver.
     *
     * @param ShipmentAddressResolverInterface $resolver
     */
    public function setAddressResolver(ShipmentAddressResolverInterface $resolver)
    {
        $this->addressResolver = $resolver;
    }

    /**
     * Returns the addressResolver.
     *
     * @return ShipmentAddressResolverInterface
     */
    public function getAddressResolver()
    {
        return $this->addressResolver;
    }
}

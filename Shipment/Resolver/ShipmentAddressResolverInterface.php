<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface ShipmentAddressResolverInterface
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentAddressResolverInterface
{
    /**
     * Resolves the shipment sender address.
     *
     * @param ShipmentInterface $shipment
     *
     * @return AddressInterface
     */
    public function resolveSenderAddress(ShipmentInterface $shipment);

    /**
     * Resolves the shipment receiver address.
     *
     * @param ShipmentInterface $shipment
     *
     * @return AddressInterface
     */
    public function resolveReceiverAddress(ShipmentInterface $shipment);
}

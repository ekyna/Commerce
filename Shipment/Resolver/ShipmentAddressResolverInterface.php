<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface ShipmentAddressResolverInterface
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentAddressResolverInterface
{
    /**
     * Returns the country repository.
     *
     * @return CountryRepositoryInterface
     */
    public function getCountryRepository(): CountryRepositoryInterface;

    /**
     * Resolves the shipment sender address.
     *
     * @param ShipmentInterface $shipment
     * @param bool              $ignoreRelay
     *
     * @return AddressInterface
     */
    public function resolveSenderAddress(ShipmentInterface $shipment, bool $ignoreRelay = false): AddressInterface;

    /**
     * Resolves the shipment receiver address.
     *
     * @param ShipmentInterface $shipment
     * @param bool              $ignoreRelay
     *
     * @return AddressInterface
     */
    public function resolveReceiverAddress(ShipmentInterface $shipment, bool $ignoreRelay = false): AddressInterface;
}

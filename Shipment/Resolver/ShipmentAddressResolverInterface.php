<?php

declare(strict_types=1);

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
    public function getCountryRepository(): CountryRepositoryInterface;

    /**
     * Resolves the shipment sender address.
     */
    public function resolveSenderAddress(ShipmentInterface $shipment, bool $ignoreRelay = false): AddressInterface;

    /**
     * Resolves the shipment receiver address.
     */
    public function resolveReceiverAddress(ShipmentInterface $shipment, bool $ignoreRelay = false): AddressInterface;
}

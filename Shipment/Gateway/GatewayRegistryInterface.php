<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Model\AddressResolverAwareInterface;
use Ekyna\Component\Commerce\Shipment\Model\WeightCalculatorAwareInterface;

/**
 * Interface GatewayRegistryInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GatewayRegistryInterface extends
    AddressResolverAwareInterface,
    WeightCalculatorAwareInterface,
    PersisterAwareInterface
{
    public function registerProvider(ProviderInterface $provider): GatewayRegistryInterface;

    public function registerPlatform(PlatformInterface $platform): GatewayRegistryInterface;

    /**
     * Returns whether a platform is registered for the given name.
     */
    public function hasPlatform(string $name): bool;

    /**
     * Returns the platform by its name.
     */
    public function getPlatform(string $name): PlatformInterface;

    /**
     * Returns all the platforms names.
     *
     * @return string[]
     */
    public function getPlatformNames(): array;

    /**
     * Returns whether or not a gateway is registered for the given name.
     */
    public function hasGateway(string $name): bool;

    /**
     * Returns the gateway by its name.
     *
     * @param string $name
     *
     * @return GatewayInterface
     *
     * @throws ShipmentGatewayException
     */
    public function getGateway(string $name): GatewayInterface;

    /**
     * Returns all the gateways.
     *
     * @return GatewayInterface[]
     */
    public function allGateways(): array;
}

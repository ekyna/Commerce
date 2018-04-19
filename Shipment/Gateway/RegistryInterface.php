<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\AddressResolverAwareInterface;
use Ekyna\Component\Commerce\Shipment\Model\WeightCalculatorAwareInterface;

/**
 * Interface GatewayRegistryInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface RegistryInterface extends
    AddressResolverAwareInterface,
    WeightCalculatorAwareInterface,
    PersisterAwareInterface
{
    /**
     * Registers the provider.
     *
     * @param ProviderInterface $provider
     *
     * @return RegistryInterface
     */
    public function registerProvider(ProviderInterface $provider);

    /**
     * Registers the platform.
     *
     * @param PlatformInterface $platform
     *
     * @return RegistryInterface
     */
    public function registerPlatform(PlatformInterface $platform);

    /**
     * Returns whether or not a platform is registered for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPlatform($name);

    /**
     * Returns the platform by its name.
     *
     * @param string $name
     *
     * @return PlatformInterface
     */
    public function getPlatform($name);

    /**
     * Returns all the platforms names.
     *
     * @return array
     */
    public function getPlatformNames();

    /**
     * Returns whether or not a gateway is registered for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasGateway($name);

    /**
     * Returns the gateway by its name.
     *
     * @param string $name
     *
     * @return GatewayInterface
     */
    public function getGateway($name);

    /**
     * Returns all the gateways.
     *
     * @return array|GatewayInterface[]
     */
    public function allGateways();
}

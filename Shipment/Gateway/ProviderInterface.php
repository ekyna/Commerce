<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

/**
 * Interface ProviderInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProviderInterface
{
    /**
     * Sets the registry.
     *
     * @param RegistryInterface $registry
     *
     * @return mixed
     */
    public function setRegistry(RegistryInterface $registry);

    /**
     * Returns whether or not a gateway is exists for the given name.
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
     * Returns the gateways.
     *
     * @return GatewayInterface[]
     */
    public function allGateways();
}

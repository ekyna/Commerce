<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

/**
 * Interface GatewayRegistryInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GatewayRegistryInterface
{
    /**
     * Registers the factory.
     *
     * @param FactoryInterface $factory
     *
     * @return GatewayRegistryInterface
     */
    public function registerFactory(FactoryInterface $factory);

    /**
     * Returns whether or not a factory is registered for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasFactory($name);

    /**
     * Returns the factory by its name.
     *
     * @param string $name
     *
     * @return FactoryInterface|mixed
     */
    public function getFactory($name);

    /**
     * Returns all the factories names.
     *
     * @return array
     */
    public function getFactoryNames();

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
     * @return GatewayInterface|mixed
     */
    public function getGateway($name);

    /**
     * Registers the gateway.
     *
     * @param GatewayInterface $gateway
     *
     * @return GatewayRegistryInterface
     */
    public function registerGateway(GatewayInterface $gateway);

    /**
     * Creates the gateway.
     *
     * @param string $factoryName
     * @param string $gatewayName
     * @param array  $config
     *
     * @return mixed
     */
    public function createGateway($factoryName, $gatewayName, array $config);
}

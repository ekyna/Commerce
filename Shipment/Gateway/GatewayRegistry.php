<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class GatewayRegistry
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GatewayRegistry implements GatewayRegistryInterface
{
    /**
     * @var array|FactoryInterface[]
     */
    private $factories;

    /**
     * @var array|GatewayInterface[]
     */
    private $gateways;


    /**
     * Constructor.
     *
     * @param array|FactoryInterface[] $factories
     * @param array|GatewayInterface[] $gateways
     */
    public function __construct(array $factories = [], array $gateways = [])
    {
        $this->factories = [];
        $this->gateways = [];

        foreach ($gateways as $gateway) {
            $this->registerGateway($gateway);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerFactory(FactoryInterface $factory)
    {
        if ($this->hasFactory($name = $factory->getName())) {
            throw new InvalidArgumentException(sprintf("A shipment factory is registered for the name '%s'.", $name));
        }

        $this->factories[$name] = $factory;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasFactory($name)
    {
        return isset($this->factories[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getFactory($name)
    {
        if (!$this->hasFactory($name)) {
            throw new InvalidArgumentException(sprintf("Shipment factory '%s' is not registered.", $name));
        }

        return $this->factories[$name];
    }

    /**
     * Returns all the factories names.
     *
     * @return array
     */
    public function getFactoryNames()
    {
        return array_map(function(FactoryInterface $factory) {
            return $factory->getName();
        }, $this->factories);
    }

    /**
     * @inheritdoc
     */
    public function createGateway($factoryName, $gatewayName, array $config)
    {
        $factory = $this->getFactory($factoryName);

        $gateway = $factory->createGateway($gatewayName, $config);

        $this->registerGateway($gateway);

        return $gateway;
    }

    /**
     * @inheritdoc
     */
    public function registerGateway(GatewayInterface $gateway)
    {
        if ($this->hasGateway($name = $gateway->getName())) {
            throw new InvalidArgumentException(sprintf("A shipment gateway is registered for the name '%s'.", $name));
        }

        $this->gateways[$name] = $gateway;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasGateway($name)
    {
        return isset($this->gateways[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getGateway($name)
    {
        if (!$this->hasGateway($name)) {
            throw new InvalidArgumentException(sprintf("Shipment gateway '%s' is not registered.", $name));
        }

        return $this->gateways[$name];
    }
}

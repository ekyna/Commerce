<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class GatewayRegistry
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Registry implements RegistryInterface
{
    use Shipment\AddressResolverAwareTrait,
        Shipment\WeightCalculatorAwareTrait,
        PersisterAwareTrait;

    /**
     * @var array|ProviderInterface[]
     */
    private $providers = [];

    /**
     * @var array|PlatformInterface[]
     */
    private $platforms = [];


    /**
     * Constructor.
     *
     * @param array|ProviderInterface[] $providers
     * @param array|PlatformInterface[] $platforms
     */
    public function __construct(array $providers = [], array $platforms = [])
    {
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }

        foreach ($platforms as $platform) {
            $this->registerPlatform($platform);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerProvider(ProviderInterface $provider)
    {
        $provider->setRegistry($this);

        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function registerPlatform(PlatformInterface $platform)
    {
        if ($this->hasPlatform($name = $platform->getName())) {
            throw new InvalidArgumentException(sprintf("A shipment platform is registered for the name '%s'.", $name));
        }

        $platform->setRegistry($this);

        $this->platforms[$name] = $platform;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasPlatform($name)
    {
        return isset($this->platforms[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getPlatform($name)
    {
        if (!$this->hasPlatform($name)) {
            throw new InvalidArgumentException(sprintf("Shipment platform '%s' is not registered.", $name));
        }

        return $this->platforms[$name];
    }

    /**
     * Returns all the platforms names.
     *
     * @return array
     */
    public function getPlatformNames()
    {
        return array_map(function (PlatformInterface $platform) {
            return $platform->getName();
        }, $this->platforms);
    }

    /**
     * @inheritdoc
     */
    public function hasGateway($name)
    {
        foreach ($this->providers as $provider) {
            if ($provider->hasGateway($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getGateway($name)
    {
        foreach ($this->providers as $provider) {
            if ($provider->hasGateway($name)) {
                return $provider->getGateway($name);
            }
        }

        throw new InvalidArgumentException(sprintf("Shipment gateway '%s' is not registered.", $name));
    }

    /**
     * @inheritDoc
     */
    public function allGateways()
    {
        $gateways = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->allGateways() as $gateway) {
                $gateways[] = $gateway;
            }
        }

        return $gateways;
    }
}

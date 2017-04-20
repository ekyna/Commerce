<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class GatewayRegistry
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GatewayRegistry implements GatewayRegistryInterface
{
    use PersisterAwareTrait;
    use Shipment\AddressResolverAwareTrait;
    use Shipment\WeightCalculatorAwareTrait;

    /** @var array<ProviderInterface> */
    private array $providers = [];

    /** @var array<PlatformInterface> */
    private array $platforms = [];


    public function __construct(array $providers = [], array $platforms = [])
    {
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }

        foreach ($platforms as $platform) {
            $this->registerPlatform($platform);
        }
    }

    public function registerProvider(ProviderInterface $provider): GatewayRegistryInterface
    {
        $provider->setRegistry($this);

        $this->providers[] = $provider;

        return $this;
    }

    public function registerPlatform(PlatformInterface $platform): GatewayRegistryInterface
    {
        if ($this->hasPlatform($name = $platform->getName())) {
            throw new InvalidArgumentException(sprintf("A shipment platform is registered for the name '%s'.", $name));
        }

        $platform->setRegistry($this);

        $this->platforms[$name] = $platform;

        return $this;
    }

    public function hasPlatform(string $name): bool
    {
        return isset($this->platforms[$name]);
    }

    public function getPlatform(string $name): PlatformInterface
    {
        if (!$this->hasPlatform($name)) {
            throw new InvalidArgumentException(sprintf("Shipment platform '%s' is not registered.", $name));
        }

        return $this->platforms[$name];
    }

    public function getPlatformNames(): array
    {
        return array_map(function (PlatformInterface $platform) {
            return $platform->getName();
        }, $this->platforms);
    }

    public function hasGateway(string $name): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->hasGateway($name)) {
                return true;
            }
        }

        return false;
    }

    public function getGateway(string $name): GatewayInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->hasGateway($name)) {
                return $provider->getGateway($name);
            }
        }

        throw new ShipmentGatewayException(sprintf("Shipment gateway '%s' is not registered.", $name));
    }

    public function allGateways(): array
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

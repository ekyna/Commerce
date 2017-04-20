<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class AbstractProvider
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractProvider implements ProviderInterface
{
    private GatewayRegistryInterface $registry;
    /** @var array<GatewayInterface> */
    private array $gateways    = [];
    private bool  $initialized = false;

    public function setRegistry(GatewayRegistryInterface $registry): void
    {
        $this->registry = $registry;
    }

    public function hasGateway(string $name): bool
    {
        $this->initialize();

        return isset($this->gateways[$name]);
    }

    public function getGateway(string $name): GatewayInterface
    {
        $this->initialize();

        if (!$this->hasGateway($name)) {
            throw new InvalidArgumentException(sprintf("Shipment gateway '%s' is not registered.", $name));
        }

        return $this->gateways[$name];
    }

    public function allGateways(): array
    {
        $this->initialize();

        return $this->gateways;
    }

    /**
     * Loads the gateways.
     */
    abstract protected function loadGateways(): void;

    /**
     * Creates and registers a gateway.
     */
    protected function createAndRegisterGateway(string $platformName, string $gatewayName, array $config): void
    {
        $platform = $this->registry->getPlatform($platformName);

        $gateway = $platform->createGateway($gatewayName, $config);

        if ($gateway instanceof Shipment\AddressResolverAwareInterface) {
            $gateway->setAddressResolver($this->registry->getAddressResolver());
        }
        if ($gateway instanceof Shipment\WeightCalculatorAwareInterface) {
            $gateway->setWeightCalculator($this->registry->getWeightCalculator());
        }
        if ($gateway instanceof PersisterAwareInterface) {
            $gateway->setPersister($this->registry->getPersister());
        }

        $this->gateways[$gatewayName] = $gateway;
    }

    /**
     * Initializes the provider.
     */
    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->loadGateways();

        $this->initialized = true;
    }
}

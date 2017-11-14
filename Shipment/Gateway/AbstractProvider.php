<?php

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
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var array|GatewayInterface[]
     */
    private $gateways = [];

    /**
     * @var bool
     */
    private $initialized = false;


    /**
     * @inheritDoc
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function hasGateway($name)
    {
        $this->initialize();

        return isset($this->gateways[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getGateway($name)
    {
        $this->initialize();

        if (!$this->hasGateway($name)) {
            throw new InvalidArgumentException(sprintf("Shipment gateway '%s' is not registered.", $name));
        }

        return $this->gateways[$name];
    }

    /**
     * @inheritDoc
     */
    public function allGateways()
    {
        $this->initialize();

        return $this->gateways;
    }

    /**
     * Loads the gateways.
     */
    abstract protected function loadGateways();

    /**
     * Creates and registers a gateway.
     *
     * @param string $platformName
     * @param string $name
     * @param array  $config
     */
    protected function createAndRegisterGateway($platformName, $name, array $config)
    {
        $platform = $this->registry->getPlatform($platformName);

        $gateway = $platform->createGateway($name, $config);

        if ($gateway instanceof Shipment\AddressResolverAwareInterface) {
            $gateway->setAddressResolver($this->registry->getAddressResolver());
        }
        if ($gateway instanceof Shipment\WeightCalculatorAwareInterface) {
            $gateway->setWeightCalculator($this->registry->getWeightCalculator());
        }

        $this->gateways[$name] = $gateway;
    }

    /**
     * Initializes the provider.
     */
    private function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->loadGateways();

        $this->initialized = true;
    }
}

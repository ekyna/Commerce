<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * Interface PlatformInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PlatformInterface
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
     * Returns the platform name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns gateway config definition.
     *
     * @return NodeInterface
     */
    public function getConfigDefinition();

    /**
     * Processes the given gateway configuration.
     *
     * @param array $config The gateway config
     *
     * @throws \Ekyna\Component\Commerce\Exception\InvalidArgumentException
     */
    public function processGatewayConfig(array $config);

    /**
     * Creates the gateway.
     *
     * @param string $name   The gateway name
     * @param array  $config The gateway config
     *
     * @return GatewayInterface
     */
    public function createGateway($name, array $config = []);

    /**
     * Executes the given action.
     *
     * @param ActionInterface $action
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function execute(ActionInterface $action);

    /**
     * Returns whether the given action is supported.
     *
     * @param ActionInterface $action
     *
     * @return bool
     */
    public function supports(ActionInterface $action);

    /**
     * Returns the FQCN of the supported actions.
     *
     * @return array
     */
    public function getActions();
}

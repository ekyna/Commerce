<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Symfony\Component\Config\Definition\NodeInterface;

/**
 * Interface PlatformInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PlatformInterface
{
    /**
     * Returns the platform name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the platform capabilities.
     *
     * @return array
     */
    public function getActions();

    /**
     * Exports the given shipments.
     *
     * @param array $shipments The shipments to export
     *
     * @return string The exported file path.
     */
    public function export(array $shipments);

    /**
     * Imports the given tracking information.
     *
     * @param string $path The file path to import
     *
     * @return bool Whether the importation succeed.
     */
    public function import($path);

    /**
     * Sets the registry.
     *
     * @param RegistryInterface $registry
     *
     * @return mixed
     */
    public function setRegistry(RegistryInterface $registry);

    /**
     * Returns gateway config definition.
     *
     * @return NodeInterface
     */
    public function getConfigDefinition();

    /**
     * Returns gateway config defaults.
     *
     * @return array
     */
    public function getConfigDefaults();

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
     * Returns whether the given action is supported.
     *
     * @param string $action
     *
     * @return bool
     */
    public function supports(string $action);
}

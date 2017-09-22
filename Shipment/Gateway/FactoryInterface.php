<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Symfony\Component\Config\Definition\NodeInterface;

/**
 * Interface FactoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface FactoryInterface
{
    /**
     * Returns the factory name.
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
}

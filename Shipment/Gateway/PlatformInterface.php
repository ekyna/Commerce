<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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
     */
    public function getName(): string;

    /**
     * Returns the platform capabilities.
     */
    public function getActions(): array;

    /**
     * Exports the given shipments.
     *
     * @param array $shipments The shipments to export
     *
     * @return string The exported file path.
     */
    public function export(array $shipments): string;

    /**
     * Imports the given tracking information.
     *
     * @param string $path The file path to import
     *
     * @return bool Whether the importation succeed.
     */
    public function import(string $path): bool;

    /**
     * Sets the registry.
     */
    public function setRegistry(GatewayRegistryInterface $registry): void;

    /**
     * Returns gateway config definition.
     */
    public function getConfigDefinition(): NodeInterface;

    /**
     * Returns gateway config defaults.
     */
    public function getConfigDefaults(): array;

    /**
     * Processes the given gateway configuration.
     *
     * @throws InvalidArgumentException
     */
    public function processGatewayConfig(array $config): array;

    /**
     * Creates the gateway.
     */
    public function createGateway(string $name, array $config = []): GatewayInterface;

    /**
     * Returns whether the given action is supported.
     */
    public function supports(string $action): bool;
}

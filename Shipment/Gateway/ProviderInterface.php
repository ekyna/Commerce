<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

/**
 * Interface ProviderInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProviderInterface
{
    public function setRegistry(GatewayRegistryInterface $registry): void;

    /**
     * Returns whether a gateway is exists for the given name.
     */
    public function hasGateway(string $name): bool;

    /**
     * Returns the gateway by its name.
     */
    public function getGateway(string $name): GatewayInterface;

    /**
     * @return array<GatewayInterface>
     */
    public function allGateways(): array;
}

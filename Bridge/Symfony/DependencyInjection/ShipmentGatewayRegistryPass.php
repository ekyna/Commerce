<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ShipmentGatewayRegistryPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentGatewayRegistryPass implements CompilerPassInterface
{
    public const PLATFORM_TAG = 'ekyna_commerce.shipment.gateway_platform';
    public const PROVIDER_TAG = 'ekyna_commerce.shipment.gateway_provider';

    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('ekyna_commerce.registry.shipment_gateway');

        // Registers the platforms
        foreach ($container->findTaggedServiceIds(self::PLATFORM_TAG, true) as $serviceId => $tags) {
            $definition->addMethodCall('registerPlatform', [new Reference($serviceId)]);
        }

        // Registers the providers
        foreach ($container->findTaggedServiceIds(self::PROVIDER_TAG, true) as $serviceId => $tags) {
            $definition->addMethodCall('registerProvider', [new Reference($serviceId)]);
        }
    }
}

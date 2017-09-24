<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ShipmentGatewayRegistryPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentGatewayRegistryPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_commerce.shipment.gateway_registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('ekyna_commerce.shipment.gateway_registry');

        // Registers the platforms
        $platforms = $container->findTaggedServiceIds('ekyna_commerce.shipment.gateway_platform');
        foreach ($platforms as $id => $attributes) {
            $registryDefinition->addMethodCall('registerPlatform', [new Reference($id)]);
        }

        // Registers the providers
        $providers = $container->findTaggedServiceIds('ekyna_commerce.shipment.gateway_provider');
        foreach ($providers as $id => $attributes) {
            $registryDefinition->addMethodCall('registerProvider', [new Reference($id)]);
        }
    }
}

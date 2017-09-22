<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RegisterShipmentGatewayFactoryPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegisterShipmentGatewayFactoryPass implements CompilerPassInterface
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

        $factories = $container->findTaggedServiceIds('ekyna_commerce.shipment_gateway_factory');

        foreach ($factories as $id => $attributes) {
            // Registers the factory
            $registryDefinition->addMethodCall('registerFactory', [new Reference($id)]);
        }
    }
}

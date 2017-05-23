<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler;

use Ekyna\Component\Commerce\Pricing\Api\PricingApi;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PricingApiPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingApiPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_commerce.pricing.api')) {
            return;
        }

        $apiDefinition = $container->getDefinition('ekyna_commerce.pricing.api');

        // Register API providers
        $providers = [];
        $services = $container->findTaggedServiceIds(PricingApi::PROVIDER_TAG);
        foreach ($services as $id => $attributes) {
            $providers[] = new Reference($id);
        }

        $apiDefinition->replaceArgument(0, $providers);
    }
}

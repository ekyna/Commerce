<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Ekyna\Component\Commerce\Pricing\Api\PricingApi;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PricingApiPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingApiPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('ekyna_commerce.api.pricing');

        // Register API providers
        $providers = [];
        foreach ($container->findTaggedServiceIds(PricingApi::PROVIDER_TAG, true) as $serviceId => $tags) {
            $providers[] = new Reference($serviceId);
        }

        $definition->replaceArgument(0, $providers);
    }
}

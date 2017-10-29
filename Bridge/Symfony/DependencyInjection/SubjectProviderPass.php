<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SubjectProviderPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_commerce.subject.provider_registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('ekyna_commerce.subject.provider_registry');

        $providers = $container->findTaggedServiceIds('ekyna_commerce.subject_provider');

        foreach ($providers as $id => $attributes) {
            // Register the provider
            $registryDefinition->addMethodCall('addProvider', [new Reference($id)]);
        }
    }
}

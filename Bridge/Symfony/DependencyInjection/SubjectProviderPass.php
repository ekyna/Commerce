<?php

declare(strict_types=1);

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
    public const TAG = 'ekyna_commerce.subject_provider';

    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('ekyna_commerce.registry.subject_provider');

        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $tags) {
            // Register the provider
            $definition->addMethodCall('addProvider', [new Reference($id)]);
        }
    }
}

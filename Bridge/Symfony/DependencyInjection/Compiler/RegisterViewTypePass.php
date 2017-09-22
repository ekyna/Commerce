<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RegisterViewTypePass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegisterViewTypePass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_commerce.common.view_type_registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('ekyna_commerce.common.view_type_registry');

        $builders = $container->findTaggedServiceIds('ekyna_commerce.view_type');

        foreach ($builders as $id => $attributes) {
            // Register the view type
            $registryDefinition->addMethodCall('addType', [new Reference($id)]);
        }
    }
}

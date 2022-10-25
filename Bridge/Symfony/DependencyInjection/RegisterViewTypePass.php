<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RegisterViewTypePass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegisterViewTypePass implements CompilerPassInterface
{
    public const VIEW_TYPE_TAG = 'ekyna_commerce.view_type';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('ekyna_commerce.registry.view_type');

        foreach ($container->findTaggedServiceIds(self::VIEW_TYPE_TAG) as $serviceId => $attributes) {
            $definition->addMethodCall('addType', [new Reference($serviceId)]);
        }
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function realpath;
use function sprintf;

/**
 * Class ConfigureValidatorPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigureValidatorPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('validator.builder');

        $names = [
            'cart',
            'common',
            'customer',
            'invoice',
            'newsletter',
            'order',
            'payment',
            'pricing',
            'quote',
            'report',
            'shipment',
            'stock',
            'subject',
            'supplier',
            'support',
        ];

        $paths = [];
        foreach ($names as $name) {
            $paths[] = realpath(__DIR__ . sprintf('/../Resources/validation/%s.xml', $name));
        }

        $definition->addMethodCall('addXmlMappings', [$paths]);
    }
}

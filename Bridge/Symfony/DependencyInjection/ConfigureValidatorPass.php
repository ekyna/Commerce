<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ConfigureValidatorPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigureValidatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('validator.builder')) {
            throw new ServiceNotFoundException('Validation is not enabled.');
        }

        $validatorBuilder = $container->getDefinition('validator.builder');

        $names = [
            'cart',
            'common',
            'invoice',
            'customer',
            'order',
            'payment',
            'pricing',
            'quote',
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

        $validatorBuilder->addMethodCall('addXmlMappings', [$paths]);
    }
}

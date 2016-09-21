<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ConfigureValidatorPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler
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

        // TODO
        $paths = [];
        $names = [
            'cart',
            'common',
            'customer',
            'order',
            'payment',
            'pricing',
            'product',
            'quote',
            //'shipment',
        ];
        foreach ($names as $name) {
            $paths[] = realpath(__DIR__ . sprintf('/../../Resources/validation/%s.xml', $name));
        }

        $validatorBuilder->addMethodCall('addXmlMappings', [$paths]);
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ReportRegistryPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportRegistryPass implements CompilerPassInterface
{
    public const FETCHER_TAG = 'ekyna_commerce.report_fetcher';
    public const SECTION_TAG = 'ekyna_commerce.report_section';
    public const WRITER_TAG  = 'ekyna_commerce.report_writer';

    public function process(ContainerBuilder $container): void
    {
        /** TODO Use ServiceLocatorTagPass::register() ?
         *       See \Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\NewsletterRegistriesPass
         */

        $definition = $container->getDefinition('ekyna_commerce.report.registry');

        $map = [
            /** @see \Ekyna\Component\Commerce\Report\ReportRegistry::registerFetcher */
            self::FETCHER_TAG => 'registerFetcher',
            /** @see \Ekyna\Component\Commerce\Report\ReportRegistry::registerSection */
            self::SECTION_TAG => 'registerSection',
            /** @see \Ekyna\Component\Commerce\Report\ReportRegistry::registerWriter */
            self::WRITER_TAG  => 'registerWriter',
        ];

        foreach ($map as $tag => $method) {
            foreach ($container->findTaggedServiceIds($tag) as $serviceId => $attributes) {
                $definition->addMethodCall($method, [new Reference($serviceId)]);
            }
        }
    }
}

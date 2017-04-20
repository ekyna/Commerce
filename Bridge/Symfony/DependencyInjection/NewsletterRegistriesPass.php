<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Synchronizer\SynchronizerInterface;
use Ekyna\Component\Commerce\Newsletter\Webhook\HandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

use function array_keys;
use function call_user_func;
use function is_subclass_of;

/**
 * Class NewsletterRegistriesPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterRegistriesPass implements CompilerPassInterface
{
    public const GATEWAY_TAG      = 'ekyna_commerce.newsletter_gateway';
    public const SYNCHRONIZER_TAG = 'ekyna_commerce.newsletter_synchronizer';
    public const HANDLER_TAG      = 'ekyna_commerce.newsletter_handler';


    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ekyna_commerce.newsletter.registry.gateway')) {
            return;
        }

        $this->register($container, self::GATEWAY_TAG, GatewayInterface::class, 'ekyna_commerce.newsletter.registry.gateway');
        $this->register($container, self::SYNCHRONIZER_TAG, SynchronizerInterface::class, 'ekyna_commerce.newsletter.registry.synchronizer');
        $this->register($container, self::HANDLER_TAG, HandlerInterface::class, 'ekyna_commerce.newsletter.registry.webhook');
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $tag
     * @param string           $serviceClass
     * @param string           $registryId
     */
    private function register(ContainerBuilder $container, string $tag, string $serviceClass, string $registryId): void
    {
        $map = [];

        $ids = array_keys($container->findTaggedServiceIds($tag));
        foreach ($ids as $id) {
            $class = $container->getDefinition($id)->getClass();

            if (!is_subclass_of($class, $serviceClass)) {
                throw new LogicException("Class $class must implements $serviceClass");
            }

            $name = call_user_func([$class, 'getName']);

            if (isset($map[$name])) {
                throw new LogicException("Service '$name' is already registered.");
            }

            $map[$name] = new Reference($id);
        }

        $container
            ->getDefinition($registryId)
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $map, 'commerce_newsletter_gateways'))
            ->replaceArgument(1, array_keys($map));
    }
}

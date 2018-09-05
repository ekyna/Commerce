<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection;

use Ekyna\Component\Commerce\Bridge\Payum\Action;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\Offline as Offline;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance as Outstanding;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Class PayumCompilerPass
 * @package Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PayumBuilderPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('payum.builder')) {
            return;
        }

        $this->registerFactories($container);
        $this->registerActions($container);
    }

    /**
     * Registers the required factories.
     *
     * @param ContainerBuilder $container
     */
    private function registerFactories(ContainerBuilder $container)
    {
        $defaultConfig = [];

        $builder = $container->getDefinition('payum.builder');

        $builder->addMethodCall('addGatewayFactoryConfig', [
            Offline\Constants::FACTORY_NAME,
            $defaultConfig,
        ]);
        $builder->addMethodCall('addGatewayFactory', [
            Offline\Constants::FACTORY_NAME,
            [Offline\OfflineGatewayFactory::class, 'build'],
        ]);

        $builder->addMethodCall('addGatewayFactoryConfig', [
            Outstanding\Constants::FACTORY_NAME,
            $defaultConfig,
        ]);
        $builder->addMethodCall('addGatewayFactory', [
            Outstanding\Constants::FACTORY_NAME,
            [Outstanding\OutstandingGatewayFactory::class, 'build'],
        ]);

        $builder->addMethodCall('addGatewayFactoryConfig', [
            Credit\Constants::FACTORY_NAME,
            $defaultConfig,
        ]);
        $builder->addMethodCall('addGatewayFactory', [
            Credit\Constants::FACTORY_NAME,
            [Credit\CreditGatewayFactory::class, 'build'],
        ]);
    }

    /**
     * Registers the payum actions.
     *
     * @param ContainerBuilder $container
     */
    private function registerActions(ContainerBuilder $container)
    {
        // Payzen convert action
        if (class_exists('Ekyna\Component\Payum\Payzen\PayzenGatewayFactory')) {
            // Convert action
            $definition = new Definition('Ekyna\Component\Commerce\Bridge\Payum\Payzen\Action\ConvertAction');
            $definition->addTag('payum.action', ['factory' => 'payzen', 'prepend' => true]);
            $container->setDefinition('ekyna_commerce.payum.action.payzen.convert_payment', $definition);

            // Fraud level action
            $definition = new Definition('Ekyna\Component\Commerce\Bridge\Payum\Payzen\Action\FraudLevelAction');
            $definition->addTag('payum.action', ['factory' => 'payzen', 'prepend' => true]);
            $container->setDefinition('ekyna_commerce.payum.action.payzen.fraud_level', $definition);
        }

        // Sips
        if (class_exists('Ekyna\Component\Payum\Sips\SipsGatewayFactory')) {
            // Convert action
            $definition = new Definition('Ekyna\Component\Commerce\Bridge\Payum\Sips\Action\ConvertAction');
            $definition->addTag('payum.action', ['factory' => 'atos_sips', 'prepend' => true]);
            $container->setDefinition('ekyna_commerce.payum.action.sips.convert_payment', $definition);
        }

        // PayPal EC NVP
        if (class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory')) {
            // Convert action
            $definition = new Definition('Ekyna\Component\Commerce\Bridge\Payum\Paypal\Action\EcNvpConvertAction');
            $definition->setArgument(0, new Reference('ekyna_commerce.common.amount_calculator'));
            if ($container->has('ekyna_setting.manager') && class_exists('Ekyna\Bundle\AdminBundle\Settings\GeneralSettingsSchema')) {
                $definition->setArgument(1, new Expression(
                    "service('ekyna_setting.manager').getParameter('general.site_name')"
                ));
            }
            $definition->addTag('payum.action', ['factory' => 'paypal_express_checkout', 'prepend' => true]);
            $container->setDefinition('ekyna_commerce.payum.action.paypal_ec_nvp.convert_payment', $definition);
        }

        // Global actions
        $actions = [
            'capture_payment' => Action\CaptureAction::class,
            'notify_payment'  => Action\NotifyAction::class,
            'status_payment'  => Action\StatusAction::class,
        ];
        foreach ($actions as $name => $class) {
            $definition = new Definition($class);
            $definition->addTag('payum.action', ['all' => true, 'prepend' => true]);

            $container->setDefinition('ekyna_commerce.payum.action.' . $name, $definition);
        }
    }
}

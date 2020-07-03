<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Offline;

use Ekyna\Component\Commerce\Bridge\Payum\Offline\Action;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;

/**
 * Class OfflineGatewayFactory
 * @package Ekyna\Component\Commerce\Bridge\Payum\Offline
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfflineGatewayFactory extends GatewayFactory
{
    /**
     * Builds a new factory.
     *
     * @param array                   $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return static
     */
    public static function build(array $defaultConfig, GatewayFactoryInterface $coreGatewayFactory = null)
    {
        return new static($defaultConfig, $coreGatewayFactory);
    }

    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name'           => Constants::FACTORY_NAME,
            'payum.factory_title'          => 'Offline',
            'payum.action.accept'          => new Action\AcceptAction(),
            'payum.action.cancel'          => new Action\CancelAction(),
            'payum.action.capture'         => new Action\CaptureAction(),
            'payum.action.convert_payment' => new Action\ConvertAction(),
            'payum.action.hang'            => new Action\HangAction(),
            'payum.action.reject'          => new Action\RejectAction(),
            'payum.action.status'          => new Action\StatusAction(),
            'payum.action.sync'            => new Action\SyncAction(),
        ]);

        $defaultOptions = ['authorize' => false,];

        $config->defaults($defaultOptions);

        $config['payum.default_options'] = $defaultOptions;

        if ($config['authorize']) {
            $config['payum.action.authorize_payment'] = new Action\AuthorizeAction();
        } else {
            unset($config['payum.action.authorize_payment']);
        }
    }
}

<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Noop;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Class NullPlatform
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Noop
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NoopPlatform extends AbstractPlatform
{
    const NAME = 'noop';


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @inheritDoc
     */
    public function createGateway($name, array $config = [])
    {
        return new NoopGateway($name, $this->processGatewayConfig($config));
    }
}

<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Noop;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Class NoopFactory
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Noop
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NoopFactory extends AbstractFactory
{
    const NAME = 'Noop';


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'noop';
    }

    /**
     * @inheritDoc
     */
    public function createGateway($name, array $config = [])
    {
        return new NoopGateway($name, $this->processGatewayConfig($config));
    }

    /**
     * @inheritDoc
     */
    protected function createConfigDefinition(NodeDefinition $rootNode)
    {
    }
}

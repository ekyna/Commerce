<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\InStore;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;

/**
 * Class InStorePlatform
 * @package Ekyna\Component\Commerce\Shipment\Gateway\InStore
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InStorePlatform extends AbstractPlatform
{
    const NAME = 'in_store';


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
        return new InStoreGateway($this, $name, $this->processGatewayConfig($config));
    }

    /**
     * @inheritDoc
     */
    public function getActions()
    {
        return [];
    }
}

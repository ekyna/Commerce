<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\InStore;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractGateway;

/**
 * Class InStoreGateway
 * @package Ekyna\Component\Commerce\Shipment\Gateway\InStore
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InStoreGateway extends AbstractGateway
{
    /**
     * @inheritDoc
     */
    public function getCapabilities()
    {
        return static::CAPABILITY_SHIPMENT || static::CAPABILITY_RETURN;
    }
}

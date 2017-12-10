<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class RelayPoint
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPoint extends AbstractAction
{
    const NAME = 'relay_point';

    /**
     * @inheritDoc
     */
    static public function getScopes()
    {
        return [static::SCOPE_GATEWAY];
    }

    /**
     * @inheritDoc
     */
    static public function getName()
    {
        return static::NAME;
    }
}

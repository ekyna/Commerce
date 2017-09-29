<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class Ship
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Ship extends AbstractAction
{
    const NAME = 'ship';

    /**
     * @inheritDoc
     */
    static public function getScopes()
    {
        return [static::SCOPE_PLATFORM, static::SCOPE_GATEWAY];
    }

    /**
     * @inheritDoc
     */
    static public function getName()
    {
        return static::NAME;
    }
}

<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class Track
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Track extends AbstractAction
{
    const NAME = 'track';

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
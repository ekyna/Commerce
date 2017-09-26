<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class Export
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Export extends AbstractAction
{
    const NAME = 'export';

    /**
     * @inheritDoc
     */
    static public function getScopes()
    {
        return [static::SCOPE_GLOBAL];
    }

    /**
     * @inheritDoc
     */
    static public function getName()
    {
        return static::NAME;
    }
}

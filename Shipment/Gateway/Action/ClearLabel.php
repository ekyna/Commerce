<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class ClearLabel
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ClearLabel extends AbstractAction
{
    const NAME = 'clear_label';

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


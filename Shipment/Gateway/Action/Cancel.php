<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class Cancel
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Cancel extends AbstractAction
{
    const NAME = 'cancel';

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

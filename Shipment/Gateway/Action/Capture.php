<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class Capture
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Capture extends AbstractAction
{
    const NAME = 'capture';

    /**
     * @inheritDoc
     */
    static public function getScope()
    {
        return static::SCOPE_CHECKOUT;
    }

    /**
     * @inheritDoc
     */
    static public function getName()
    {
        return static::NAME;
    }
}

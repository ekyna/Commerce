<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class PrintLabel
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PrintLabel extends AbstractAction
{
    const NAME = 'print_label';

    /**
     * @inheritDoc
     */
    static public function getScope()
    {
        return static::SCOPE_GATEWAY;
    }

    /**
     * @inheritDoc
     */
    static public function getName()
    {
        return static::NAME;
    }
}


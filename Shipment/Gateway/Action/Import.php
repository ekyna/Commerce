<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Class Import
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Import extends AbstractAction
{
    const NAME = 'import';

    /**
     * @inheritDoc
     */
    static public function getScope()
    {
        return static::SCOPE_PLATFORM;
    }

    /**
     * @inheritDoc
     */
    static public function getName()
    {
        return static::NAME;
    }
}

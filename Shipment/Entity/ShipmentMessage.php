<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractMessage;

/**
 * Class ShipmentMessage
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMessage extends AbstractMessage
{
    /**
     * @inheritdoc
     */
    protected function getTranslationClass(): string
    {
        return ShipmentMessageTranslation::class;
    }
}

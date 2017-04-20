<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractMessage;

/**
 * Class ShipmentMessage
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMessage extends AbstractMessage
{
    protected function getTranslationClass(): string
    {
        return ShipmentMessageTranslation::class;
    }
}

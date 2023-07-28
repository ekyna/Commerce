<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class ShipmentUtil
 * @package Ekyna\Component\Commerce\Shipment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentUtil
{
    public static function hasPhysicalItem(ShipmentInterface $shipment): bool
    {
        foreach ($shipment->getItems() as $item) {
            if ($item->getSaleItem()->isPhysical()) {
                return true;
            }
        }

        return false;
    }
}

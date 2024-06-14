<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Enum StockLogTypeEnum
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
enum StockLogTypeEnum: string
{
    case SupplierDelivery = 'Supplier delivery';
    case OrderShipment    = 'Order shipment';
    case StockAdjustment  = 'Stock adjustment';
}

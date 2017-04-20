<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Interface OrderShipmentItemInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderShipmentItemInterface extends ShipmentItemInterface
{
    public function setOrderItem(?OrderItemInterface $orderItem): OrderShipmentItemInterface;

    public function getOrderItem(): ?OrderItemInterface;
}

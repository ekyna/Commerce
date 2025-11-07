<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Preparer;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface OrderPreparerInterface
 * @package Ekyna\Component\Commerce\Common\Preparer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderPreparerInterface
{
    /**
     * Prepares the sale by prioritizing the sale and building shipment at preparation state.
     *
     * @return ShipmentInterface|null The prepared shipment.
     */
    public function prepare(OrderInterface $order): ?ShipmentInterface;

    /**
     * Aborts the sale preparation by canceling the preparation shipment.
     *
     * @return ShipmentInterface|null The canceled shipment.
     */
    public function abort(OrderInterface $order): ?ShipmentInterface;
}

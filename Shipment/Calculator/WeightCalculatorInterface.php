<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Shipment\Model;

/**
 * Interface WeightCalculatorInterface
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WeightCalculatorInterface
{
    /**
     * Calculate the shipment total weight.
     */
    public function calculateShipment(Model\ShipmentInterface $shipment): Decimal;

    /**
     * Calculate the shipment item total weight.
     */
    public function calculateShipmentItem(Model\ShipmentItemInterface $item): Decimal;
}

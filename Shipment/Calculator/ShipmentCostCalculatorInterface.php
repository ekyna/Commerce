<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Calculator;

use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Interface ShipmentCostCalculatorInterface
 * @package Ekyna\Component\Commerce\Shipment\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentCostCalculatorInterface
{
    public function calculateSale(SaleInterface $sale, string $currency): Cost;

    public function calculateSubject(ShipmentSubjectInterface $subject, string $currency): Cost;

    public function calculateShipment(ShipmentInterface $shipment, string $currency): Cost;
}

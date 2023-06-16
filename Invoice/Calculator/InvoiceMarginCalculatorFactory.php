<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\ItemCostCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCostCalculatorInterface;

/**
 * Class InvoiceMarginCalculatorFactory
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceMarginCalculatorFactory
{
    /**
     * @param AmountCalculatorFactory         $calculatorFactory
     * @param ItemCostCalculatorInterface     $itemCostCalculator
     * @param ShipmentCostCalculatorInterface $shipmentCostCalculator
     * @param string                          $currency
     */
    public function __construct(
        private readonly AmountCalculatorFactory         $calculatorFactory,
        private readonly ItemCostCalculatorInterface     $itemCostCalculator,
        private readonly ShipmentCostCalculatorInterface $shipmentCostCalculator,
        private readonly string                          $currency
    ) {
    }

    public function create(): InvoiceMarginCalculatorInterface
    {
        return new InvoiceMarginCalculator(
            $this->calculatorFactory,
            $this->itemCostCalculator,
            $this->shipmentCostCalculator,
            $this->currency
        );
    }
}

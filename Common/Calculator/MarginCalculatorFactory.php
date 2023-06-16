<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCostCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;

/**
 * Class MarginCalculatorFactory
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MarginCalculatorFactory
{
    public function __construct(
        private readonly AmountCalculatorFactory         $calculatorFactory,
        private readonly ItemCostCalculatorInterface     $itemCostCalculator,
        private readonly ShipmentCostCalculatorInterface $shipmentCostCalculator,
        private readonly CurrencyConverterInterface      $currencyConverter,
    ) {
    }

    /**
     * Returns a new margin calculator.
     *
     * @param string|null     $currency The currency
     * @param StatFilter|null $filter   The item filter
     */
    public function create(
        string     $currency = null,
        StatFilter $filter = null
    ): MarginCalculatorInterface {
        $currency = $currency ?? $this->currencyConverter->getDefaultCurrency();

        $calculator = new MarginCalculator($currency, $filter);

        $calculator->setCalculatorFactory($this->calculatorFactory);
        $calculator->setItemCostCalculator($this->itemCostCalculator);
        $calculator->setShipmentCostCalculator($this->shipmentCostCalculator);

        return $calculator;
    }
}

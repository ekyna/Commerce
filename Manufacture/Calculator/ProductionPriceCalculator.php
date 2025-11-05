<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Stock\Calculator\AssignableCostCalculatorInterface;

/**
 * Class ProductionPriceCalculator
 * @package Ekyna\Component\Commerce\Manufacture\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionPriceCalculator
{
    public function __construct(
        private readonly AssignableCostCalculatorInterface $assignableCostCalculator,
    ) {
    }

    public function calculateOrderCost(ProductionOrderInterface $order): Decimal
    {
        $price = new Decimal(0);

        foreach ($order->getItems() as $item) {
            $price = $price->add(
                $this->calculateItemTotalCost($item)
            );
        }

        return $price;
    }

    public function calculateItemTotalCost(ProductionItemInterface $item): Decimal
    {
        return $this
            ->calculateItemCost($item)
            ->mul($item->getQuantity());
    }

    public function calculateItemCost(ProductionItemInterface $item): Decimal
    {
        return $this
            ->assignableCostCalculator
            ->calculateAssignableCost($item)
            ->getTotal(false);
    }

    public function calculateShippingCost(ProductionOrderInterface $order): Decimal
    {
        return new Decimal(0);
    }
}

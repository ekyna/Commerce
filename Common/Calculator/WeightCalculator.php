<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model;

/**
 * Class WeightCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WeightCalculator implements WeightCalculatorInterface
{
    public function calculateSale(Model\SaleInterface $sale): Decimal
    {
        $total = new Decimal(0);

        foreach ($sale->getItems() as $item) {
            $total += $this->calculateSaleItem($item);
        }

        return $total;
    }

    public function calculateSaleItem(Model\SaleItemInterface $item): Decimal
    {
        $total = $item->getWeight() * $item->getTotalQuantity();

        foreach ($item->getChildren() as $child) {
            $total += $this->calculateSaleItem($child);
        }

        return $total;
    }
}

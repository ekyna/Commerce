<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Class WeightCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WeightCalculator implements WeightCalculatorInterface
{
    /**
     * @inheritdoc
     */
    public function calculateSale(Model\SaleInterface $sale)
    {
        $total = .0;

        foreach ($sale->getItems() as $item) {
            $total += $this->calculateSaleItem($item);
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateSaleItem(Model\SaleItemInterface $item)
    {
        $total = $item->getWeight() * $item->getTotalQuantity();

        foreach ($item->getChildren() as $child) {
            $total += $this->calculateSaleItem($child);
        }

        return $total;
    }
}

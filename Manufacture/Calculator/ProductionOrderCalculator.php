<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Calculator;

use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;

/**
 * Class ProductionOrderCalculator
 * @package Ekyna\Component\Commerce\Manufacture\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderCalculator
{
    public function calculateProducedQuantity(ProductionOrderInterface $order): int
    {
        $quantity = 0;
        foreach ($order->getProductions() as $production) {
            $quantity += $production->getQuantity();
        }

        return $quantity;
    }
}

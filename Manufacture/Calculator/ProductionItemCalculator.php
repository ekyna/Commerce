<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Calculator;


use Decimal\Decimal;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;

/**
 * Class ProductionItemCalculator
 * @package Ekyna\Component\Commerce\Manufacture\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionItemCalculator
{
    public function calculateMissingQuantity(ProductionItemInterface $item): Decimal
    {
        $missing = new Decimal(0);
        foreach ($item->getStockAssignments() as $a) {
            $missing = $missing->add(
                $a->getSoldQuantity()->sub($a->getShippableQuantity())->sub($a->getShippedQuantity())
            );
        }

        return $missing;
    }
}

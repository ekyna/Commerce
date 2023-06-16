<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface ItemCostCalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ItemCostCalculatorInterface
{
    /**
     * Calculates the sale item unit costs.
     */
    public function calculateSaleItem(SaleItemInterface $item, Decimal $quantity = null, bool $single = false): Cost;
}

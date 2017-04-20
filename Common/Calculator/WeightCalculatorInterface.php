<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface WeightCalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WeightCalculatorInterface
{
    /**
     * Calculate the sale total weight.
     */
    public function calculateSale(Model\SaleInterface $sale): Decimal;

    /**
     * Calculate the sale item total weight.
     */
    public function calculateSaleItem(Model\SaleItemInterface $item): Decimal;
}

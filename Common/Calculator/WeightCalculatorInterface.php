<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

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
     *
     * @param Model\SaleInterface $sale
     *
     * @return float
     */
    public function calculateSale(Model\SaleInterface $sale);

    /**
     * Calculate the sale item total weight.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return float
     */
    public function calculateSaleItem(Model\SaleItemInterface $item);
}

<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface CalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CalculatorInterface
{
    /**
     * Calculate based on the net price.
     */
    const MODE_NET = 'net';

    /**
     * Calculate based on the gross price.
     */
    const MODE_GROSS = 'gross';


    /**
     * Sets the calculation mode.
     *
     * @param string $mode
     *
     * @return $this|CalculatorInterface
     */
    public function setMode($mode);

    /**
     * Calculates the sale item's amounts.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return Amounts
     */
    public function calculateSaleItemAmounts(Model\SaleItemInterface $item);

    /**
     * Calculates the sale's amounts.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Amounts
     */
    public function calculateSaleAmounts(Model\SaleInterface $sale);

    /**
     * Calculates the adjustment amounts.
     *
     * @param Model\AdjustmentInterface $adjustment
     * @param Amounts             $parentAmounts
     *
     * @return Amounts
     */
    public function calculateDiscountAdjustmentAmounts(Model\AdjustmentInterface $adjustment, Amounts $parentAmounts);
}

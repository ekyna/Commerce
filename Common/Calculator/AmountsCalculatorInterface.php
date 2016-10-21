<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface AmountsCalculatorInterface
 *
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AmountsCalculatorInterface
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
     * @return $this|AmountsCalculatorInterface
     */
    public function setMode($mode);

    /**
     * Calculates the sale amounts.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $gross
     *
     * @return Result
     */
    public function calculateSale(Model\SaleInterface $sale, $gross = false);

    /**
     * Calculates the sale shipment amounts.
     *
     * @param Model\SaleInterface $sale
     *
     * @return Result
     */
    public function calculateShipment(Model\SaleInterface $sale);

    /**
     * Calculates the sale item amounts.
     *
     * @param Model\SaleItemInterface $item
     * @param bool                    $gross
     *
     * @return Result
     */
    public function calculateSaleItem(Model\SaleItemInterface $item, $gross = false);

    /**
     * Calculates the adjustment amounts.
     *
     * @param Model\AdjustmentInterface $adjustment
     *
     * @return Result
     */
    public function calculateDiscountAdjustment(Model\AdjustmentInterface $adjustment);
}

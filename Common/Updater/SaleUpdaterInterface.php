<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface SaleUpdaterInterface
 * @package Ekyna\Component\Commerce\Common\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleUpdaterInterface
{
    /**
     * Recalculate the whole sale.
     *
     * @param SaleInterface $sale
     * @param bool          $force
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function recalculate(SaleInterface $sale, $force = false);

    /**
     * Updates the whole sale discount adjustments.
     *
     * @param SaleInterface $sale
     * @param bool          $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateDiscounts(SaleInterface $sale, $persistence = false);

    /**
     * Updates the whole sale taxation adjustments.
     *
     * @param SaleInterface $sale
     * @param bool $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTaxation(SaleInterface $sale, $persistence = false);

    /**
     * Updates the sale shipment related taxation adjustments.
     *
     * @param SaleInterface $sale
     * @param bool $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateShipmentTaxation(SaleInterface $sale, $persistence = false);

    /**
     * Updates the total weight.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTotalWeight(SaleInterface $sale);

    /**
     * Updates the total amounts.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTotalAmounts(SaleInterface $sale);

    /**
     * Updates the totals (weights and amounts).
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTotals(SaleInterface $sale);
}

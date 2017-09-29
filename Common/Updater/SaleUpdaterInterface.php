<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Model;

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
     * @param Model\SaleInterface $sale
     * @param bool                $force
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function recalculate(Model\SaleInterface $sale, $force = false);

    /**
     * Sets the sale invoice address from the given address.
     *
     * @param Model\SaleInterface    $sale
     * @param Model\AddressInterface $source
     * @param bool                   $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateInvoiceAddressFromAddress(
        Model\SaleInterface $sale,
        Model\AddressInterface $source,
        $persistence = false
    );

    /**
     * Sets the sale invoice address from the given address.
     *
     * @param Model\SaleInterface    $sale
     * @param Model\AddressInterface $source
     * @param bool                   $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateDeliveryAddressFromAddress(
        Model\SaleInterface $sale,
        Model\AddressInterface $source,
        $persistence = false
    );

    /**
     * Updates the whole sale discount adjustments.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateDiscounts(Model\SaleInterface $sale, $persistence = false);

    /**
     * Updates the whole sale taxation adjustments.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTaxation(Model\SaleInterface $sale, $persistence = false);

    /**
     * Updates the sale shipment related taxation adjustments.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateShipmentTaxation(Model\SaleInterface $sale, $persistence = false);

    /**
     * Updates the total weight.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTotalWeight(Model\SaleInterface $sale);

    /**
     * Updates the payment terms and outstanding date.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether or not the sale has been updated.
     */
    public function updateOutstandingAndTerm(Model\SaleInterface $sale);

    /**
     * Updates the paid total.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updatePaidTotal(Model\SaleInterface $sale);

    /**
     * Updates the total amounts.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTotalAmounts(Model\SaleInterface $sale);

    /**
     * Updates the totals (weights and amounts).
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTotals(Model\SaleInterface $sale);
}

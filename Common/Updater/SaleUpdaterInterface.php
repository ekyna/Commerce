<?php

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Model;
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
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function recalculate(Model\SaleInterface $sale): bool;

    /**
     * Updates the total weight.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateWeightTotal(Model\SaleInterface $sale): bool;

    /**
     * Updates the totals (content, payments and invoices).
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTotals(Model\SaleInterface $sale): bool;

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
    ): bool;

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
    ): bool;

    /**
     * Updates the whole sale discount adjustments.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateDiscounts(Model\SaleInterface $sale, $persistence = false): bool;

    /**
     * Updates the whole sale taxation adjustments.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTaxation(Model\SaleInterface $sale, $persistence = false): bool;

    /**
     * Updates the sale shipment related taxation adjustments.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $persistence
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateShipmentTaxation(Model\SaleInterface $sale, $persistence = false): bool;

    /**
     * Updates the sale shipment method and amount if needed.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool
     */
    public function updateShipmentMethodAndAmount(Model\SaleInterface $sale): bool;

    /**
     * Updates the payment terms and outstanding date.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether or not the sale has been updated.
     */
    public function updatePaymentTerm(Model\SaleInterface $sale): bool;

    /**
     * Updates the exchange rate and date.
     *
     * @param Model\SaleInterface $sale
     * @param bool                $force
     *
     * @return bool
     */
    public function updateExchangeRate(Model\SaleInterface $sale, bool $force = false): bool;

    /**
     * Updates the sale's net and grand totals.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateAmountTotals(SaleInterface $sale): bool;

    /**
     * Updates the payment totals total.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updatePaymentTotals(SaleInterface $sale): bool;

    /**
     * Updates the sale invoice total.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateInvoiceTotals(SaleInterface $sale): bool;
}

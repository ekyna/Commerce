<?php

declare(strict_types=1);

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
     * Recalculate the sale.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function recalculate(Model\SaleInterface $sale): bool;

    /**
     * Updates the total weight.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateWeightTotal(Model\SaleInterface $sale): bool;

    /**
     * Updates the totals (content, payments and invoices).
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTotals(Model\SaleInterface $sale): bool;

    /**
     * Sets the sale invoice address from the given address.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateInvoiceAddressFromAddress(
        Model\SaleInterface $sale,
        Model\AddressInterface $source,
        bool $persistence = false
    ): bool;

    /**
     * Sets the sale invoice address from the given address.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateDeliveryAddressFromAddress(
        Model\SaleInterface $sale,
        Model\AddressInterface $source,
        bool $persistence = false
    ): bool;

    /**
     * Updates the sale discount adjustments.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateDiscounts(Model\SaleInterface $sale, bool $persistence = false): bool;

    /**
     * Makes the sale discounts mutable.
     */
    public function makeDiscountsMutable(SaleInterface $sale): void;

    /**
     * Clears the sale mutable discounts.
     */
    public function clearMutableDiscounts(SaleInterface $sale): void;

    /**
     * Updates the sale taxation adjustments.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateTaxation(Model\SaleInterface $sale, bool $persistence = false): bool;

    /**
     * Updates the sale shipment related taxation adjustments.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateShipmentTaxation(Model\SaleInterface $sale, bool $persistence = false): bool;

    /**
     * Updates the sale shipment method and amount if needed.
     */
    public function updateShipmentMethodAndAmount(Model\SaleInterface $sale): bool;

    /**
     * Updates the payment terms and outstanding date.
     *
     * @return bool Whether the sale has been updated.
     */
    public function updatePaymentTerm(Model\SaleInterface $sale): bool;

    /**
     * Updates the exchange rate and date.
     */
    public function updateExchangeRate(Model\SaleInterface $sale, bool $force = false): bool;

    /**
     * Updates the sale's net and grand totals.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateAmountTotals(SaleInterface $sale): bool;

    /**
     * Updates the payment totals.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updatePaymentTotals(SaleInterface $sale): bool;

    /**
     * Updates the sale invoice total.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function updateInvoiceTotals(SaleInterface $sale): bool;
}

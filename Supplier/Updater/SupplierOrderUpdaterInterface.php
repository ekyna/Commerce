<?php

namespace Ekyna\Component\Commerce\Supplier\Updater;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Interface SupplierOrderUpdaterInterface
 * @package Ekyna\Component\Commerce\Supplier\Updater
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderUpdaterInterface
{
    /**
     * Updates the number.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether or not the supplier order number has been changed.
     */
    public function updateNumber(SupplierOrderInterface $order): bool;

    /**
     * Updates the state.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether or not the supplier order has been changed.
     */
    public function updateState(SupplierOrderInterface $order): bool;

    /**
     * Updates the payment and forwarder totals.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether or not the supplier order has been changed.
     */
    public function updateTotals(SupplierOrderInterface $order): bool;

    /**
     * Updates the order exchange rate.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the payment has been changed or not.
     */
    public function updateExchangeRate(SupplierOrderInterface $order): bool;
}

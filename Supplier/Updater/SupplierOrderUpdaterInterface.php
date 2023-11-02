<?php

declare(strict_types=1);

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
     * Updates the currency based on selected supplier.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the supplier order number has been changed.
     */
    public function updateCurrency(SupplierOrderInterface $order): bool;

    /**
     * Updates the carrier based on selected supplier.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the supplier order number has been changed.
     */
    public function updateCarrier(SupplierOrderInterface $order): bool;

    /**
     * Updates the number.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the supplier order number has been changed.
     */
    public function updateNumber(SupplierOrderInterface $order): bool;

    /**
     * Updates the payment and forwarder totals.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the supplier order has been changed.
     */
    public function updateTotals(SupplierOrderInterface $order): bool;

    /**
     * Updates the payment and forwarder paid totals.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the supplier order has been changed.
     */
    public function updatePaidTotals(SupplierOrderInterface $order): bool;

    /**
     * Updates the state.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the supplier order has been changed.
     */
    public function updateState(SupplierOrderInterface $order): bool;
}

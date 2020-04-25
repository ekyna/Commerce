<?php

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Interface SupplierOrderCalculatorInterface
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderCalculatorInterface
{
    /**
     * Calculates the supplier order's payment total.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order): float;

    /**
     * Calculates the supplier order's payment tax.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculatePaymentTax(SupplierOrderInterface $order): float;

    /**
     * Calculates the supplier order's items total.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculateItemsTotal(SupplierOrderInterface $order): float;

    /**
     * Calculates the supplier order's forwarder total.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculateForwarderTotal(SupplierOrderInterface $order): float;

    /**
     * Calculates the supplier order's weight total.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculateWeightTotal(SupplierOrderInterface $order): float;

    /**
     * Calculates the stock unit net price, converted in default currency.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return float
     */
    public function calculateStockUnitNetPrice(SupplierOrderItemInterface $item): float;

    /**
     * Calculates stock unit shipping price, converted in default currency.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return float
     */
    public function calculateStockUnitShippingPrice(SupplierOrderItemInterface $item): float;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Decimal\Decimal;
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
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order): Decimal;

    /**
     * Calculates the supplier order's payment tax.
     */
    public function calculatePaymentTax(SupplierOrderInterface $order): Decimal;

    /**
     * Calculates the supplier order's items total.
     */
    public function calculateItemsTotal(SupplierOrderInterface $order): Decimal;

    /**
     * Calculates the supplier order's forwarder total.
     */
    public function calculateForwarderTotal(SupplierOrderInterface $order): Decimal;

    /**
     * Calculates the supplier order's weight total.
     */
    public function calculateWeightTotal(SupplierOrderInterface $order): Decimal;

    /**
     * Calculates the stock unit net price, converted in default currency.
     */
    public function calculateStockUnitNetPrice(SupplierOrderItemInterface $item): Decimal;

    /**
     * Calculates stock unit shipping price, converted in default currency.
     */
    public function calculateStockUnitShippingPrice(SupplierOrderItemInterface $item): Decimal;
}

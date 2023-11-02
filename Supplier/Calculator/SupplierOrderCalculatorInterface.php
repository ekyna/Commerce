<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

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
     * Calculates the total paid to the supplier.
     */
    public function calculateSupplierPaidTotal(SupplierOrderInterface $order): Decimal;

    /**
     * Calculates the total paid to the forwarder.
     */
    public function calculateForwarderPaidTotal(SupplierOrderInterface $order): Decimal;
}

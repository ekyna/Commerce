<?php

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Interface CalculatorInterface
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CalculatorInterface
{
    /**
     * Calculates the supplier order's payment total.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order);
}

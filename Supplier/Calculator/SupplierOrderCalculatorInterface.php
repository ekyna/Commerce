<?php

namespace Ekyna\Component\Commerce\Supplier\Calculator;

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
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order);

    /**
     * Calculates the supplier order's forwarder total.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculateForwarderTotal(SupplierOrderInterface $order);

    /**
     * Calculates the supplier order's weight total.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    public function calculateWeightTotal(SupplierOrderInterface $order);
}

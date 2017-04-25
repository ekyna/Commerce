<?php

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class Calculator
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Calculator implements CalculatorInterface
{
    /**
     * @inheritdoc
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order)
    {
        $total = $order->getShippingCost();

        foreach ($order->getItems() as $item) {
            // TODO precision based on currency
            $total += round($item->getNetPrice(), 2) * $item->getQuantity();
        }

        return $total;
    }
}

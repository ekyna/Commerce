<?php

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Ekyna\Component\Commerce\Common\Util\Money;
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
            $total += $item->getNetPrice() * $item->getQuantity();
        }

        return Money::round($total, $order->getCurrency()->getCode());
    }
}

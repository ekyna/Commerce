<?php

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierOrderCalculator
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderCalculator implements SupplierOrderCalculatorInterface
{
    /**
     * @inheritdoc
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order)
    {
        $total = $order->getShippingCost();

        $currency = $order->getCurrency()->getCode();

        foreach ($order->getItems() as $item) {
            $total += Money::round($item->getNetPrice(), $currency) * $item->getQuantity();
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateWeightTotal(SupplierOrderInterface $order)
    {
        $total = 0;

        foreach ($order->getItems() as $item) {
            $total += $item->getProduct()->getWeight() * $item->getQuantity();
        }

        return $total;
    }
}

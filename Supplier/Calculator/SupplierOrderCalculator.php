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
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $defaultCurrency
     */
    public function __construct(string $defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritdoc
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order)
    {
        $total = 0;

        $currency = $order->getCurrency()->getCode();

        foreach ($order->getItems() as $item) {
            $total += Money::round($item->getNetPrice(), $currency) * $item->getQuantity();
        }

        $total += $order->getShippingCost() - $order->getDiscountTotal() + $order->getTaxTotal();

        return Money::round($total, $currency);
    }

    /**
     * @inheritdoc
     */
    public function calculateForwarderTotal(SupplierOrderInterface $order)
    {
        $total = $order->getForwarderFee() + $order->getCustomsTax() + $order->getCustomsVat();

        return Money::round($total, $this->defaultCurrency);
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

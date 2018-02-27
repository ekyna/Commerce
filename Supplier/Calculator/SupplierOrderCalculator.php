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
    public function calculatePaymentTax(SupplierOrderInterface $order)
    {
        $base = $this->calculatePaymentBase($order);

        $amount = 0;
        if (null !== $tax = $order->getSupplier()->getTax()) {
            $currency = $order->getCurrency()->getCode();

            $amount = Money::round($base * $tax->getRate() / 100, $currency);
        }

        return $amount;
    }

    /**
     * @inheritdoc
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order)
    {
        $total = $this->calculatePaymentBase($order) + $this->calculatePaymentTax($order);

        $currency = $order->getCurrency()->getCode();

        return Money::round($total, $currency);
    }

    /**
     * @inheritdoc
     */
    public function calculateItemsTotal(SupplierOrderInterface $order)
    {
        $total = 0;

        $currency = $order->getCurrency()->getCode();

        foreach ($order->getItems() as $item) {
            $total += Money::round($item->getNetPrice(), $currency) * $item->getQuantity();
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateForwarderTotal(SupplierOrderInterface $order)
    {
        if (null === $carrier = $order->getCarrier()) {
            return 0;
        }

        $base = $order->getForwarderFee();

        $taxAmount = 0;
        if (null !== $tax = $carrier->getTax()) {
            $taxAmount = Money::round($base * $tax->getRate() / 100, $this->defaultCurrency);
        }

        $total = $base + $taxAmount + $order->getCustomsTax() + $order->getCustomsVat();

        return Money::round($total, $this->defaultCurrency);
    }

    /**
     * @inheritdoc
     */
    public function calculateWeightTotal(SupplierOrderInterface $order)
    {
        $total = 0;

        foreach ($order->getItems() as $item) {
            if (null !== $product = $item->getProduct()) {
                $total += $product->getWeight() * $item->getQuantity();
            }
        }

        return $total;
    }

    /**
     * Calculates the supplier order base.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    private function calculatePaymentBase(SupplierOrderInterface $order)
    {
        $base = $this->calculateItemsTotal($order) + $order->getShippingCost() - $order->getDiscountTotal();

        $currency = $order->getCurrency()->getCode();

        return Money::round($base, $currency);
    }
}

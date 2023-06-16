<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierOrderCalculator
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderCalculator implements SupplierOrderCalculatorInterface
{
    public function __construct(
        private readonly WeightingCalculatorInterface $weightingCalculator,
        private readonly TaxResolverInterface         $taxResolver,
        private readonly string                       $defaultCurrency
    ) {
    }

    /**
     * @inheritDoc
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order): Decimal
    {
        $total = $this->calculatePaymentBase($order) + $this->calculatePaymentTax($order);

        $currency = $order->getCurrency()->getCode();

        return Money::round($total, $currency);
    }

    /**
     * @inheritDoc
     */
    public function calculatePaymentTax(SupplierOrderInterface $order): Decimal
    {
        $currency = $order->getCurrency()->getCode();

        $bases = [];

        // Items
        foreach ($order->getItems() as $item) {
            $base = $item->getNetPrice() * $item->getQuantity();

            if (0 < $discount = $order->getDiscountTotal()) {
                $weighting = $this->weightingCalculator->getWeighting($item)->price * $item->getQuantity();
                $base -= $discount * $weighting;
            }

            $taxes = $this->taxResolver->resolveTaxes($item, $order);

            foreach ($taxes as $tax) {
                if (!isset($bases[$rate = $tax->getRate()->toFixed(2)])) {
                    $bases[$rate] = new Decimal(0);
                }

                $bases[$rate] += $base;
            }
        }

        // Shipping
        if (null !== $tax = $order->getSupplier()->getTax()) {
            if (!isset($bases[$rate = $tax->getRate()->toFixed(2)])) {
                $bases[$rate] = new Decimal(0);
            }

            $bases[$rate] += $order->getShippingCost();
        }

        // Calculation
        $total = new Decimal(0);
        foreach ($bases as $rate => $base) {
            $total += Money::round($base * $rate / 100, $currency);
        }

        return $total;
    }

    /**
     * @inheritDoc
     */
    public function calculateItemsTotal(SupplierOrderInterface $order): Decimal
    {
        $total = new Decimal(0);

        $currency = $order->getCurrency()->getCode();

        foreach ($order->getItems() as $item) {
            $total += Money::round($item->getNetPrice() * $item->getQuantity(), $currency);
        }

        return $total;
    }

    /**
     * @inheritDoc
     */
    public function calculateForwarderTotal(SupplierOrderInterface $order): Decimal
    {
        if (null === $carrier = $order->getCarrier()) {
            return new Decimal(0);
        }

        $base = $order->getForwarderFee();

        $taxAmount = new Decimal(0);
        if ($tax = $carrier->getTax()) {
            $taxAmount = Money::round($base * $tax->getRate() / 100, $this->defaultCurrency);
        }

        $total = $base + $taxAmount + $order->getCustomsTax() + $order->getCustomsVat();

        return Money::round($total, $this->defaultCurrency);
    }

    /**
     * @inheritDoc
     */
    public function calculateWeightTotal(SupplierOrderInterface $order): Decimal
    {
        $total = new Decimal(0);

        foreach ($order->getItems() as $item) {
            $total += $item->getWeight() * $item->getQuantity();
        }

        return $total;
    }

    /**
     * Calculates the supplier order base.
     */
    private function calculatePaymentBase(SupplierOrderInterface $order): Decimal
    {
        $base = $this->calculateItemsTotal($order) + $order->getShippingCost() - $order->getDiscountTotal();

        return Money::round($base, $order->getCurrency()->getCode());
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use DateTime;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Exception;

use function spl_object_id;

/**
 * Class SupplierOrderCalculator
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderCalculator implements SupplierOrderCalculatorInterface
{
    private CurrencyConverterInterface $currencyConverter;
    private TaxResolverInterface $taxResolver;
    private array $cache;

    public function __construct(CurrencyConverterInterface $currencyConverter, TaxResolverInterface $taxResolver)
    {
        $this->currencyConverter = $currencyConverter;
        $this->taxResolver = $taxResolver;

        $this->onClear();
    }

    /**
     * Clears the weighting cache.
     */
    public function onClear(): void
    {
        $this->cache = [];
    }

    public function calculatePaymentTotal(SupplierOrderInterface $order): Decimal
    {
        $total = $this->calculatePaymentBase($order) + $this->calculatePaymentTax($order);

        $currency = $order->getCurrency()->getCode();

        return Money::round($total, $currency);
    }

    public function calculatePaymentTax(SupplierOrderInterface $order): Decimal
    {
        $currency = $order->getCurrency()->getCode();

        $bases = [];

        // Items
        foreach ($order->getItems() as $item) {
            $base = $item->getNetPrice() * $item->getQuantity();

            if (0 < $discount = $order->getDiscountTotal()) {
                $weight = $this->getWeighting($item, false) * $item->getQuantity();
                $base -= $discount * $weight;
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

        $currency = $this->currencyConverter->getDefaultCurrency();
        $base = $order->getForwarderFee();

        $taxAmount = new Decimal(0);
        if ($tax = $carrier->getTax()) {
            $taxAmount = Money::round($base * $tax->getRate() / 100, $currency);
        }

        $total = $base + $taxAmount + $order->getCustomsTax() + $order->getCustomsVat();

        return Money::round($total, $currency);
    }

    public function calculateWeightTotal(SupplierOrderInterface $order): Decimal
    {
        $total = new Decimal(0);

        foreach ($order->getItems() as $item) {
            $total += $item->getWeight() * $item->getQuantity();
        }

        return $total;
    }

    public function calculateStockUnitNetPrice(SupplierOrderItemInterface $item): Decimal
    {
        if (null === $order = $item->getOrder()) {
            throw new StockLogicException('Supplier order item\'s order must be set at this point.');
        }

        if ($order->getDiscountTotal()->isZero()) {
            return $this->convertPrice($item->getNetPrice(), $order, false)->round(5);
        }

        $discount = $order->getDiscountTotal() * $this->getWeighting($item, false);

        return $this->convertPrice($item->getNetPrice() - $discount, $order, false)->round(5);
    }

    public function calculateStockUnitShippingPrice(SupplierOrderItemInterface $item): Decimal
    {
        if (null === $order = $item->getOrder()) {
            throw new StockLogicException('Supplier order item\'s order must be set at this point.');
        }

        $total = $this->convertPrice($order->getShippingCost(), $order)
            + $order->getForwarderFee()
            + $order->getCustomsTax();

        if ($total->isZero()) {
            return $total;
        }

        return $total->mul($this->getWeighting($item))->round(5);
    }

    /**
     * Calculates the supplier order base.
     */
    private function calculatePaymentBase(SupplierOrderInterface $order): Decimal
    {
        $base = $this->calculateItemsTotal($order) + $order->getShippingCost() - $order->getDiscountTotal();

        return Money::round($base, $order->getCurrency()->getCode());
    }

    /**
     * Converts the given price in default currency.
     *
     * @throws Exception
     */
    private function convertPrice(Decimal $price, SupplierOrderInterface $order, bool $round = true): Decimal
    {
        $currency = $order->getCurrency()->getCode();

        if ($currency === $this->currencyConverter->getDefaultCurrency()) {
            return $price;
        }

        if ($rate = $order->getExchangeRate()) {
            return $this
                ->currencyConverter
                ->convertWithRate($price, (new Decimal(1))->div($rate), null, $round);
        }

        $date = $order->getPaymentDate();
        if ($date > new DateTime()) {
            $date = null;
        }

        return $this
            ->currencyConverter
            ->convert($price, $currency, null, $date, $round);
    }

    /**
     * Returns the item weighting.
     *
     * @throws StockLogicException
     */
    private function getWeighting(SupplierOrderItemInterface $item, bool $byWeight = true): Decimal
    {
        if (null === $order = $item->getOrder()) {
            throw new StockLogicException("Supplier order item's order must be set at this point.");
        }

        $orderKey = spl_object_id($order);
        if (!isset($this->cache[$orderKey])) {
            $this->calculateWeighting($order);
        }

        $cache = $this->cache[$orderKey];
        $itemKey = spl_object_id($item);

        if ($byWeight && !$cache['no_weight']) {
            return $cache['items'][$itemKey]['weight'];
        }

        if (!$cache['no_price']) {
            return $cache['items'][$itemKey]['price'];
        }

        return $cache['items'][$itemKey]['quantity'];
    }

    /**
     * Calculates order item's weighting.
     */
    private function calculateWeighting(SupplierOrderInterface $order): void
    {
        $orderKey = spl_object_id($order);

        if (isset($this->cache[$orderKey])) {
            return;
        }

        $amount = [];
        $total = [
            'weight'   => new Decimal(0),
            'price'    => new Decimal(0),
            'quantity' => new Decimal(0),
        ];

        // Gather amounts and totals
        $noWeight = $noPrice = true;
        foreach ($order->getItems() as $item) {
            $amount[spl_object_id($item)] = [
                'weight'   => $weight = $item->getWeight(),
                'price'    => $price = $item->getNetPrice(),
                'quantity' => 1,
            ];

            $quantity = $item->getQuantity();

            $total['weight'] += $weight * $quantity;
            $total['price'] += $price * $quantity;
            $total['quantity'] += $quantity;

            if (0 < $weight) { // TODO Use packaging format
                $noWeight = false;
            }

            if (0 < $price) {
                $noPrice = false;
            }
        }

        // Calculate weighting
        $items = [];
        foreach ($amount as $id => $data) {
            $items[$id] = [];
            foreach (['weight', 'price', 'quantity'] as $key) {
                if (0 < $total[$key]) {
                    $items[$id][$key] = $data[$key] / $total[$key];
                } else {
                    $items[$id][$key] = new Decimal(0);
                }
            }
        }

        $this->cache[$orderKey] = [
            'items'     => $items,
            'no_weight' => $noWeight,
            'no_price'  => $noPrice,
        ];
    }
}

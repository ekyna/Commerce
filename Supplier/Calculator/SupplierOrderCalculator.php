<?php

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Class SupplierOrderCalculator
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderCalculator implements SupplierOrderCalculatorInterface
{
    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var array
     */
    private $cache;


    /**
     * Constructor.
     *
     * @param CurrencyConverterInterface $currencyConverter
     * @param TaxResolverInterface       $taxResolver
     */
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

    /**
     * @inheritdoc
     */
    public function calculatePaymentTotal(SupplierOrderInterface $order): float
    {
        $total = $this->calculatePaymentBase($order) + $this->calculatePaymentTax($order);

        $currency = $order->getCurrency()->getCode();

        return Money::round($total, $currency);
    }

    /**
     * @inheritdoc
     */
    public function calculatePaymentTax(SupplierOrderInterface $order): float
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
                if (!isset($bases[$rate = $tax->getRate()])) {
                    $bases[$rate] = 0;
                }

                $bases[$rate] += $base;
            }
        }

        // Shipping
        if (null !== $tax = $order->getSupplier()->getTax()) {
            if (!isset($bases[$rate = $tax->getRate()])) {
                $bases[$rate] = 0;
            }

            $bases[$rate] += $order->getShippingCost();
        }

        // Calculation
        $total = 0;
        foreach ($bases as $rate => $base) {
            $total += Money::round($base * $rate / 100, $currency);
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateItemsTotal(SupplierOrderInterface $order): float
    {
        $total = 0;

        $currency = $order->getCurrency()->getCode();

        foreach ($order->getItems() as $item) {
            $total += Money::round($item->getNetPrice() * $item->getQuantity(), $currency);
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateForwarderTotal(SupplierOrderInterface $order): float
    {
        if (null === $carrier = $order->getCarrier()) {
            return 0.;
        }

        $currency = $this->currencyConverter->getDefaultCurrency();
        $base = $order->getForwarderFee();

        $taxAmount = 0;
        if (null !== $tax = $carrier->getTax()) {
            $taxAmount = Money::round($base * $tax->getRate() / 100, $currency);
        }

        $total = $base + $taxAmount + $order->getCustomsTax() + $order->getCustomsVat();

        return Money::round($total, $currency);
    }

    /**
     * @inheritdoc
     */
    public function calculateWeightTotal(SupplierOrderInterface $order): float
    {
        $total = 0.;

        foreach ($order->getItems() as $item) {
            $total += $item->getWeight() * $item->getQuantity();
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateStockUnitNetPrice(SupplierOrderItemInterface $item): float
    {
        if (null === $order = $item->getOrder()) {
            throw new StockLogicException("Supplier order item's order must be set at this point.");
        }

        $currency = $order->getCurrency()->getCode();

        if (1 !== Money::compare($order->getDiscountTotal(), 0, $currency)) {
            return round($this->convertPrice($item->getNetPrice(), $order, false), 5);
        }

        $discount = $order->getDiscountTotal() * $this->getWeighting($item, false);

        return round($this->convertPrice($item->getNetPrice() - $discount, $order, false), 5);
    }

    /**
     * @inheritdoc
     */
    public function calculateStockUnitShippingPrice(SupplierOrderItemInterface $item): float
    {
        if (null === $order = $item->getOrder()) {
            throw new StockLogicException("Supplier order item's order must be set at this point.");
        }

        $currency = $order->getCurrency()->getCode();

        $total = $this->convertPrice($order->getShippingCost(), $order)
            + $order->getForwarderFee() + $order->getCustomsTax();

        if (0 === Money::compare($total, 0, $currency)) {
            return 0.;
        }

        return round($total * $this->getWeighting($item), 5);
    }

    /**
     * Calculates the supplier order base.
     *
     * @param SupplierOrderInterface $order
     *
     * @return float
     */
    private function calculatePaymentBase(SupplierOrderInterface $order): float
    {
        $base = $this->calculateItemsTotal($order) + $order->getShippingCost() - $order->getDiscountTotal();

        $currency = $order->getCurrency()->getCode();

        return Money::round($base, $currency);
    }

    /**
     * Converts the given price in default currency.
     *
     * @param float                  $price
     * @param SupplierOrderInterface $order
     * @param bool                   $round
     *
     * @return float
     * @throws \Exception
     */
    private function convertPrice(float $price, SupplierOrderInterface $order, bool $round = true): float
    {
        $currency = $order->getCurrency()->getCode();

        if ($currency === $this->currencyConverter->getDefaultCurrency()) {
            return $price;
        }

        if ($rate = $order->getExchangeRate()) {
            return $this
                ->currencyConverter
                ->convertWithRate($price, 1 / $rate, null, $round);
        }

        $date = $order->getPaymentDate();
        if ($date > new \DateTime()) {
            $date = null;
        }

        return $this
            ->currencyConverter
            ->convert($price, $currency, null, $date, $round);
    }

    /**
     * Returns the item weighting.
     *
     * @param SupplierOrderItemInterface $item
     * @param bool                       $byWeight
     *
     * @return float
     *
     * @throws StockLogicException
     */
    private function getWeighting(SupplierOrderItemInterface $item, bool $byWeight = true): float
    {
        if (null === $order = $item->getOrder()) {
            throw new StockLogicException("Supplier order item's order must be set at this point.");
        }

        $orderKey = spl_object_hash($order);
        if (!isset($this->cache[$orderKey])) {
            $this->calculateWeighting($order);
        }

        $cache = $this->cache[$orderKey];
        $itemKey = spl_object_hash($item);

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
     *
     * @param SupplierOrderInterface $order
     */
    private function calculateWeighting(SupplierOrderInterface $order): void
    {
        $orderKey = spl_object_hash($order);

        if (isset($this->cache[$orderKey])) {
            return;
        }

        $currency = $order->getCurrency()->getCode();

        $amount = [];
        $total = [
            'weight'   => 0,
            'price'    => 0,
            'quantity' => 0,
        ];

        // Gather amounts and totals
        $noWeight = $noPrice = true;
        foreach ($order->getItems() as $item) {
            $amount[spl_object_hash($item)] = [
                'weight'   => $weight = $item->getWeight(),
                'price'    => $price = $item->getNetPrice(),
                'quantity' => 1,
            ];

            $quantity = $item->getQuantity();

            $total['weight'] += $weight * $quantity;
            $total['price'] += $price * $quantity;
            $total['quantity'] += $quantity;

            if (1 === bccomp($weight, 0, 5)) { // TODO Use packaging format
                $noWeight = false;
            }

            if (1 === Money::compare($price, 0, $currency)) {
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
                    $items[$id][$key] = 0;
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

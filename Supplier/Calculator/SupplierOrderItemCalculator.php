<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use DateTime;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Exception;

/**
 * Class SupplierOrderItemCalculator
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemCalculator implements SupplierOrderItemCalculatorInterface
{
    public function __construct(
        private readonly WeightingCalculatorInterface $weightingCalculator,
        private readonly CurrencyConverterInterface   $currencyConverter,
    ) {
    }

    public function calculateItemProductPrice(SupplierOrderItemInterface $item): Decimal
    {
        if (null === $order = $item->getOrder()) {
            throw new LogicException('Supplier order item\'s order must be set at this point.');
        }

        if ($order->getDiscountTotal()->isZero()) {
            $price = $item->getNetPrice()->div($item->getPacking());
        } else {
            $weighting = $this->weightingCalculator->getWeighting($item)->price;

            $discount = $order->getDiscountTotal() * $weighting;

            $price = $item->getNetPrice()->sub($discount)->div($item->getPacking());
        }

        return $this->convertPrice($price, $order, false)->round(5);
    }

    public function calculateItemShippingPrice(SupplierOrderItemInterface $item): Decimal
    {
        if (null === $order = $item->getOrder()) {
            throw new LogicException('Supplier order item\'s order must be set at this point.');
        }

        $total = $this->convertPrice($order->getShippingCost(), $order)
            + $order->getForwarderFee()
            + $order->getCustomsTax();

        if ($total->isZero()) {
            return $total;
        }

        $weighting = $this->weightingCalculator->getWeighting($item)->resolve();

        return $total->mul($weighting)->div($item->getPacking())->round(5);
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
}

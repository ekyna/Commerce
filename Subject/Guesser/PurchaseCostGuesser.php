<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Guesser;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;

/**
 * Class PurchaseCostGuesser
 * @package Ekyna\Component\Commerce\Subject\Guesser
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PurchaseCostGuesser implements PurchaseCostGuesserInterface
{
    protected RepositoryFactoryInterface $repositoryFactory;
    protected CurrencyConverterInterface $currencyConverter;


    public function __construct(
        RepositoryFactoryInterface $repositoryFactory,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->currencyConverter = $currencyConverter;
    }

    public function guess(SubjectInterface $subject, string $quote = null, bool $shipping = false): ?Decimal
    {
        if (is_null($quote)) {
            $quote = $this->currencyConverter->getDefaultCurrency();
        }

        // By assignable stock units (avg)
        if ($cost = $this->getLatestOpenedStockUnitsCost($subject, $quote, $shipping)) {
            return $cost;
        }

        // By latest closed stock unit
        if ($cost = $this->getLatestClosedStockUnitCost($subject, $quote, $shipping)) {
            return $cost;
        }

        // By latest supplier order item
        if ($cost = $this->getLatestSupplierOrderItemPrice($subject, $quote)) {
            return $cost;
        }

        // By supplier products (avg)
        return $this->getSupplierProductAverageCost($subject, $quote);
    }

    /**
     * Returns the latest opened stock unit cost price.
     *
     * @param SubjectInterface $subject  The subject
     * @param string           $quote    The quote currency
     * @param bool             $shipping Whether to include shipping cost
     */
    private function getLatestOpenedStockUnitsCost(SubjectInterface $subject, string $quote, bool $shipping): ?Decimal
    {
        if (!$subject instanceof StockSubjectInterface) {
            return null;
        }

        if (!$repository = $this->getStockUnitRepository($subject)) {
            return null;
        }

        $units = $repository->findLatestNotClosedBySubject($subject);

        if (empty($units)) {
            return null;
        }

        foreach ($units as $unit) {
            if ($this->shouldSkipUnit($unit)) {
                continue;
            }

            if ($cost = $this->calculateStockUnitCost($unit, $quote, $shipping)) {
                continue;
            }

            return $cost;
        }

        return null;
    }

    /**
     * Returns the latest closed stock unit cost price.
     *
     * @TODO Limit to past 6 months (configurable)
     */
    private function getLatestClosedStockUnitCost(SubjectInterface $subject, string $quote, bool $shipping): ?Decimal
    {
        if (!$subject instanceof StockSubjectInterface) {
            return null;
        }

        if (!$repository = $this->getStockUnitRepository($subject)) {
            return null;
        }

        $units = $repository->findLatestClosedBySubject($subject, 3);

        foreach ($units as $unit) {
            if ($this->shouldSkipUnit($unit)) {
                continue;
            }

            return $this->calculateStockUnitCost($unit, $quote, $shipping);
        }

        return null;
    }

    /**
     * Returns whether the stock unit should be skipped.
     */
    private function shouldSkipUnit(StockUnitInterface $unit): bool
    {
        if (null === $order = $unit->getSupplierOrder()) {
            return true;
        }

        if (0 < $order->getShippingCost()) {
            return false;
        }

        // Skip orders from foreign countries not having shipping cost.
        $country = $order->getSupplier()->getAddress()->getCountry()->getCode();
        if ($country != $this->getCountryRepository()->getDefaultCode()) {
            return true;
        }

        return false;
    }

    /**
     * Calculates the stock unit cost price.
     */
    private function calculateStockUnitCost(StockUnitInterface $unit, string $quote, bool $shipping = false): ?Decimal
    {
        $price = $unit->getNetPrice();

        if ($shipping) {
            $price += $unit->getShippingPrice();
        }

        if ($price->isZero()) {
            return null;
        }

        $base = $this->currencyConverter->getDefaultCurrency();

        if ($order = $unit->getSupplierOrder()) {
            // Convert with order's exchange rate
            $rate = $this->currencyConverter->getSubjectExchangeRate($order, $base, $quote);

            return $this->currencyConverter->convertWithRate($price, $rate, $quote, false);
        }

        return $this->currencyConverter->convert($price, $base, $quote);
    }

    /**
     * Returns the given subject's stock unit repository.
     */
    private function getStockUnitRepository(StockSubjectInterface $subject): ?StockUnitRepositoryInterface
    {
        $class = $subject::getStockUnitClass();

        $repository = $this->repositoryFactory->getRepository($class);

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new UnexpectedTypeException($repository, StockUnitRepositoryInterface::class);
        }

        return $repository;
    }

    /**
     * Returns the latest supplier order item cost price.
     *
     * @param SubjectInterface $subject The subject
     * @param string           $quote   The quote currency
     */
    private function getLatestSupplierOrderItemPrice(SubjectInterface $subject, string $quote): ?Decimal
    {
        $item = $this->getSupplierOrderItemRepository()->findLatestOrderedBySubject($subject);

        if (null === $item) {
            return null;
        }

        $cost = $item->getNetPrice();

        if ($cost->isZero()) {
            return null;
        }

        $base = $item->getOrder()->getCurrency()->getCode();

        // Convert with current exchange rate
        return $this->currencyConverter->convert($cost, $base, $quote);
    }

    /**
     * Returns the supplier product average cost price.
     *
     * @param SubjectInterface $subject The subject
     * @param string           $quote   The quote currency
     */
    private function getSupplierProductAverageCost(SubjectInterface $subject, string $quote): ?Decimal
    {
        $products = $this->getSupplierProductRepository()->findBySubject($subject);

        if (empty($products)) {
            return null;
        }

        $cost = new Decimal(0);
        $count = 0;
        foreach ($products as $product) {
            $price = $product->getNetPrice();

            if ($price->isZero()) {
                continue;
            }

            $count++;

            $base = $product->getSupplier()->getCurrency()->getCode();

            // Convert with current exchange rate
            $cost += $this->currencyConverter->convert($price, $base, $quote);
        }

        return $this->average($cost, $count, $quote);
    }

    /**
     * Returns the average cost price average.
     */
    private function average(Decimal $total, int $count, string $currency): ?Decimal
    {
        if ($total->isZero()) {
            return null;
        }

        if (1 < $count) {
            $total = $total->div($count);
        }

        return Money::round($total, $currency);
    }

    private function getSupplierOrderItemRepository(): SupplierOrderItemRepositoryInterface
    {
        return $this->repositoryFactory->getRepository(SupplierOrderItemInterface::class);
    }

    private function getSupplierProductRepository(): SupplierProductRepositoryInterface
    {
        return $this->repositoryFactory->getRepository(SupplierProductInterface::class);
    }

    private function getCountryRepository(): CountryRepositoryInterface
    {
        return $this->repositoryFactory->getRepository(CountryInterface::class);
    }
}

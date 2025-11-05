<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Guesser;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderItemCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;

/**
 * Class SubjectCostGuesserInterface
 * @package Ekyna\Component\Commerce\Subject\Guesser
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectCostGuesser implements SubjectCostGuesserInterface
{
    public function __construct(
        private readonly RepositoryFactoryInterface           $repositoryFactory,
        private readonly SupplierOrderItemCalculatorInterface $supplierOrderItemCalculator,
        private readonly CurrencyConverterInterface           $currencyConverter,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function guess(SubjectInterface $subject): ?Cost
    {
        // By assignable stock units (avg)
        if ($cost = $this->getLatestOpenedStockUnitsCost($subject)) {
            return $cost;
        }

        // By latest closed stock unit
        if ($cost = $this->getLatestClosedStockUnitCost($subject)) {
            return $cost;
        }

        // By latest supplier order item
        if ($cost = $this->getLatestSupplierOrderItemPrice($subject)) {
            return $cost;
        }

        // By supplier products (avg)
        return $this->getLatestSupplierProductCost($subject);
    }

    /**
     * Returns the latest opened stock unit cost price.
     *
     * @param SubjectInterface $subject The subject
     */
    private function getLatestOpenedStockUnitsCost(SubjectInterface $subject): ?Cost
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

            if ($cost = $this->calculateStockUnitCost($unit)) {
                return $cost;
            }
        }

        return null;
    }

    /**
     * Returns the latest closed stock unit cost price.
     *
     * @TODO Limit to past 6 months (configurable)
     */
    private function getLatestClosedStockUnitCost(SubjectInterface $subject): ?Cost
    {
        if (!$subject instanceof StockSubjectInterface) {
            return null;
        }

        if (!$repository = $this->getStockUnitRepository($subject)) {
            return null;
        }

        $units = $repository->findLatestClosedBySubject($subject);

        foreach ($units as $unit) {
            if ($this->shouldSkipUnit($unit)) {
                continue;
            }

            return $this->calculateStockUnitCost($unit);
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
        if ($country !== $this->getCountryRepository()->getDefaultCode()) {
            return true;
        }

        return false;
    }

    /**
     * Calculates the stock unit cost price.
     */
    private function calculateStockUnitCost(StockUnitInterface $unit): ?Cost
    {
        $price = clone $unit->getNetPrice();
        $shipping = clone $unit->getShippingPrice();

        if ($price->isZero() && $shipping->isZero()) {
            return null;
        }

        return new Cost(
            $price,
            $shipping,
        );
    }

    /**
     * Returns the latest supplier order item cost price.
     *
     * @param SubjectInterface $subject The subject
     */
    private function getLatestSupplierOrderItemPrice(SubjectInterface $subject): ?Cost
    {
        $item = $this->getSupplierOrderItemRepository()->findLatestOrderedBySubject($subject);

        if (null === $item) {
            return null;
        }

        $price = $this->supplierOrderItemCalculator->calculateItemProductPrice($item);
        $shipping = $this->supplierOrderItemCalculator->calculateItemShippingPrice($item);

        if ($price->isZero() && $shipping->isZero()) {
            return null;
        }

        return new Cost(
            $price,
            $shipping,
            average: true
        );
    }

    /**
     * Returns the latest supplier product cost price.
     *
     * @param SubjectInterface $subject The subject
     */
    private function getLatestSupplierProductCost(SubjectInterface $subject): ?Cost
    {
        $product = $this
            ->getSupplierProductRepository()
            ->findLatestWithPriceBySubject($subject);

        if (null === $product) {
            return null;
        }

        $price = $product->getNetPrice();

        $base = $product->getSupplier()->getCurrency()->getCode();

        // Convert with current exchange rate
        // TODO Use historical exchange rate
        $cost = $this->currencyConverter->convert($price, $base);

        return new Cost($cost, average: true);
    }

    /**
     * Returns the given subject's stock unit repository.
     */
    private function getStockUnitRepository(StockSubjectInterface $subject): ?StockUnitRepositoryInterface
    {
        // TODO use \Ekyna\Component\Commerce\Stock\Helper\StockUnitHelper::getRepository
        $class = $subject::getStockUnitClass();

        $repository = $this->repositoryFactory->getRepository($class);

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new UnexpectedTypeException($repository, StockUnitRepositoryInterface::class);
        }

        return $repository;
    }

    private function getSupplierOrderItemRepository(): SupplierOrderItemRepositoryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repositoryFactory->getRepository(SupplierOrderItemInterface::class);
    }

    private function getSupplierProductRepository(): SupplierProductRepositoryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repositoryFactory->getRepository(SupplierProductInterface::class);
    }

    private function getCountryRepository(): CountryRepositoryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repositoryFactory->getRepository(CountryInterface::class);
    }
}

<?php

namespace Ekyna\Component\Commerce\Subject\Guesser;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;

/**
 * Class PurchaseCostGuesser
 * @package Ekyna\Component\Commerce\Subject\Guesser
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PurchaseCostGuesser implements PurchaseCostGuesserInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SupplierOrderItemRepositoryInterface
     */
    protected $supplierOrderItemRepository;

    /**
     * @var SupplierProductRepositoryInterface
     */
    protected $supplierProductRepository;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface               $entityManager
     * @param SupplierOrderItemRepositoryInterface $supplierOrderItemRepository
     * @param SupplierProductRepositoryInterface   $supplierProductRepository
     * @param CurrencyConverterInterface           $currencyConverter
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SupplierOrderItemRepositoryInterface $supplierOrderItemRepository,
        SupplierProductRepositoryInterface $supplierProductRepository,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->entityManager = $entityManager;
        $this->supplierOrderItemRepository = $supplierOrderItemRepository;
        $this->supplierProductRepository = $supplierProductRepository;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @inheritdoc
     */
    public function guess(SubjectInterface $subject, string $quote = null, bool $shipping = false): ?float
    {
        if (is_null($quote)) {
            $quote = $this->currencyConverter->getDefaultCurrency();
        }

        // By assignable stock units (avg)
        if (null !== $cost = $this->getAssignableStockUnitsCost($subject, $quote, $shipping)) {
            return $cost;
        }

        // By latest closed stock unit
        if (null !== $cost = $this->getLatestClosedStockUnitCost($subject, $quote, $shipping)) {
            return $cost;
        }

        // By latest supplier order item
        if (null !== $cost = $this->getLatestSupplierOrderItemPrice($subject, $quote)) {
            return $cost;
        }

        // By supplier products (avg)
        return $this->getSupplierProductAverageCost($subject, $quote);
    }

    /**
     * Returns the subject assignable stock units average cost price.
     *
     * @param SubjectInterface $subject  The subject
     * @param string           $quote    The quote currency
     * @param bool             $shipping Whether to include shipping cost
     *
     * @return float|null
     */
    private function getAssignableStockUnitsCost(SubjectInterface $subject, string $quote, bool $shipping): ?float
    {
        if (!$subject instanceof StockSubjectInterface) {
            return null;
        }

        if (!$repository = $this->getStockUnitRepository($subject)) {
            return null;
        }

        $units = $repository->findAssignableBySubject($subject);

        if (empty($units)) {
            return null;
        }

        $total = $count = 0;

        foreach ($units as $unit) {
            if (null === $cost = $this->calculateStockUnitCost($unit, $quote, $shipping)) {
                continue;
            }

            $total += $cost;

            $count++;
        }

        return $this->average($total, $count, $quote);
    }

    /**
     * Returns the latest closed stock unit cost price.
     *
     * @param SubjectInterface $subject
     * @param string           $quote
     * @param bool             $shipping
     *
     * @return float|null
     *
     * @TODO Limit to past 6 months (configurable)
     */
    private function getLatestClosedStockUnitCost(SubjectInterface $subject, string $quote, bool $shipping): ?float
    {
        if (!$subject instanceof StockSubjectInterface) {
            return null;
        }

        if (!$repository = $this->getStockUnitRepository($subject)) {
            return null;
        }

        $units = $repository->findLatestClosedBySubject($subject, 1);

        if (empty($units)) {
            return null;
        }

        $unit = current($units);

        return $this->calculateStockUnitCost($unit, $quote, $shipping);
    }

    /**
     * Calculates the stock unit cost price.
     *
     * @param StockUnitInterface $unit
     * @param string             $quote
     * @param bool               $shipping
     *
     * @return float|null
     */
    private function calculateStockUnitCost(StockUnitInterface $unit, string $quote, bool $shipping = false): ?float
    {
        $price = $unit->getNetPrice();

        if ($shipping) {
            $price += $unit->getShippingPrice();
        }

        if (1 !== bccomp($price, 0, 5)) {
            return null;
        }

        $base = $this->currencyConverter->getDefaultCurrency();

        if (null !== $order = $unit->getSupplierOrder()) {
            // Convert with order's exchange rate
            $rate = $this->currencyConverter->getSubjectExchangeRate($order, $base, $quote);

            return $this->currencyConverter->convertWithRate($price, $rate, $quote, false);
        }

        return $this->currencyConverter->convert($price, $base, $quote);
    }

    /**
     * Returns the given subject's stock unit repository.
     *
     * @param StockSubjectInterface $subject
     *
     * @return StockUnitRepositoryInterface|null
     */
    private function getStockUnitRepository(StockSubjectInterface $subject): ?StockUnitRepositoryInterface
    {
        $class = $subject::getStockUnitClass();

        $repository = $this->entityManager->getRepository($class);

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
     *
     * @return float|null
     */
    private function getLatestSupplierOrderItemPrice(SubjectInterface $subject, string $quote): ?float
    {
        $item = $this->supplierOrderItemRepository->findLatestOrderedBySubject($subject);

        if (null === $item) {
            return null;
        }

        $cost = $item->getNetPrice();

        if (1 !== bccomp($cost, 0, 5)) {
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
     *
     * @return float|null
     */
    private function getSupplierProductAverageCost(SubjectInterface $subject, string $quote): ?float
    {
        $products = $this->supplierProductRepository->findBySubject($subject);

        if (empty($products)) {
            return null;
        }

        $cost = $count = 0;
        foreach ($products as $product) {
            $price = $product->getNetPrice();

            if (1 !== bccomp($price, 0, 5)) {
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
     *
     * @param float  $total
     * @param int    $count
     * @param string $currency
     *
     * @return float|null
     */
    private function average(float $total, int $count, string $currency): ?float
    {
        if (1 !== bccomp($total, 0, 5)) {
            return null;
        }

        if (1 < $count) {
            $total = round($total / $count, 5);
        }

        return Money::round($total, $currency);
    }
}

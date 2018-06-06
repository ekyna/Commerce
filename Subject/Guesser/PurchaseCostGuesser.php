<?php

namespace Ekyna\Component\Commerce\Subject\Guesser;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
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
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface               $entityManager
     * @param SupplierOrderItemRepositoryInterface $supplierOrderItemRepository
     * @param SupplierProductRepositoryInterface   $supplierProductRepository
     * @param CurrencyConverterInterface           $currencyConverter
     * @param string                               $defaultCurrency
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SupplierOrderItemRepositoryInterface $supplierOrderItemRepository,
        SupplierProductRepositoryInterface $supplierProductRepository,
        CurrencyConverterInterface $currencyConverter,
        string $defaultCurrency
    ) {
        $this->entityManager = $entityManager;
        $this->supplierOrderItemRepository = $supplierOrderItemRepository;
        $this->supplierProductRepository = $supplierProductRepository;
        $this->currencyConverter = $currencyConverter;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritdoc
     */
    public function guess(SubjectInterface $subject, $quoteCurrency = null)
    {
        // By assignable stock units (avg)
        if ($subject instanceof StockSubjectInterface) {
            $class = $subject::getStockUnitClass();

            /** @var StockUnitRepositoryInterface $repository */
            $repository = $this->entityManager->getRepository($class);

            $units = $repository->findAssignableBySubject($subject);

            if (!empty($units)) {
                $cost = $count = 0;

                foreach ($units as $unit) {
                    if (0 < $netPrice = $unit->getNetPrice()) {
                        $cost += $netPrice;
                        $count++;
                    }
                }

                if (1 < $count) {
                    $cost = round($cost / $count, 5);
                }

                if (0 < $cost) {
                    return $this->currencyConverter->convert($cost, $this->defaultCurrency, $quoteCurrency);
                }
            }
        }

        // By latest supplier order item
        $item = $this->supplierOrderItemRepository->findLatestOrderedBySubject($subject);
        if (null !== $item && 0 < $netPrice = $item->getNetPrice()) {
            $c = $item->getOrder()->getCurrency()->getCode();
            return $this->currencyConverter->convert($netPrice, $c, $quoteCurrency);
        }

        // By supplier products (avg)
        $products = $this->supplierProductRepository->findBySubject($subject);
        if (!empty($products)) {
            $cost = $count = 0;

            foreach ($products as $product) {
                if (0 < $netPrice = $product->getNetPrice()) {
                    $c = $product->getSupplier()->getCurrency()->getCode();
                    $cost += $this->currencyConverter->convert($netPrice, $c, $quoteCurrency);
                    $count++;
                }
            }

            if (1 < $count) {
                $cost = round($cost / $count, 5);
            }

            return $cost;
        }

        return null;
    }
}

<?php

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitLinker
 * @package Ekyna\Component\Commerce\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitLinker implements StockUnitLinkerInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var SupplierOrderCalculatorInterface
     */
    protected $calculator;

    /**
     * @var StockUnitUpdaterInterface
     */
    protected $stockUnitUpdater;

    /**
     * @var StockUnitResolverInterface
     */
    protected $unitResolver;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     * @param SupplierOrderCalculatorInterface $calculator
     * @param StockUnitUpdaterInterface        $stockUnitUpdater
     * @param StockUnitResolverInterface       $unitResolver
     * @param CurrencyConverterInterface       $currencyConverter
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        SupplierOrderCalculatorInterface $calculator,
        StockUnitUpdaterInterface $stockUnitUpdater,
        StockUnitResolverInterface $unitResolver,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->calculator = $calculator;
        $this->stockUnitUpdater = $stockUnitUpdater;
        $this->unitResolver = $unitResolver;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @inheritdoc
     */
    public function linkItem(SupplierOrderItemInterface $item): void
    {
        if (!$item->hasSubjectIdentity()) {
            return;
        }

        // Find 'unlinked' stock units ordered (+ Cached 'new' stock units look up)
        if (null === $unit = $this->unitResolver->findLinkable($item)) {
            // Not found -> create a new stock unit
            $unit = $this->unitResolver->createBySubjectRelative($item);
        }

        $unit
            ->setSupplierOrderItem($item)
            ->setWarehouse($item->getOrder()->getWarehouse());

        $this->stockUnitUpdater->updateOrdered($unit, $item->getQuantity(), false);

        $this->updateData($item);
    }

    /**
     * @inheritdoc
     */
    public function applyItem(SupplierOrderItemInterface $item): bool
    {
        if (!$item->hasSubjectIdentity()) {
            return false;
        }

        // Supplier order item has been previously linked to a stock unit.
        $unit = $item->getStockUnit();

        $changed = false;

        // Update ordered quantity if needed
        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            $cs = $this->persistenceHelper->getChangeSet($item, 'quantity');
            if (0 != $cs[1] - $cs[0]) { // TODO Use packaging format
                $this->stockUnitUpdater->updateOrdered($unit, $item->getQuantity(), false);
                $changed = true;
            }
        }

        $this->updateData($item);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function unlinkItem(SupplierOrderItemInterface $item): void
    {
        if (!$item->hasSubjectIdentity()) {
            return;
        }

        if (null === $unit = $item->getStockUnit()) {
            return;
        }

        $unit
            // Set supplier order item to null
            ->setSupplierOrderItem(null)
            ->setWarehouse(null)
            // Clear calculated data from supplier order item
            ->setNetPrice(0)
            ->setShippingPrice(0)
            ->setEstimatedDateOfArrival(null);

        // Set ordered quantity to zero
        $this->stockUnitUpdater->updateOrdered($unit, 0, false);
    }

    /**
     * @inheritdoc
     */
    public function updateData(SupplierOrderItemInterface $item): void
    {
        if (!$item->hasSubjectIdentity()) {
            return;
        }

        if (null === $unit = $item->getStockUnit()) {
            return;
        }

        $price = $this->calculator->calculateStockUnitNetPrice($item);
        $shipping = $this->calculator->calculateStockUnitShippingPrice($item);
        $eda = $item->getOrder()->getEstimatedDateOfArrival();

        $this->stockUnitUpdater->updateNetPrice($unit, $price);
        $this->stockUnitUpdater->updateShippingPrice($unit, $shipping);
        $this->stockUnitUpdater->updateEstimatedDateOfArrival($unit, $eda);
    }
}

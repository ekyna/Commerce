<?php

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
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
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockUnitUpdaterInterface  $stockUnitUpdater
     * @param StockUnitResolverInterface $unitResolver
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitUpdaterInterface $stockUnitUpdater,
        StockUnitResolverInterface $unitResolver,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUnitUpdater = $stockUnitUpdater;
        $this->unitResolver = $unitResolver;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @inheritdoc
     */
    public function linkItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        // Find 'unlinked' stock units ordered (+ Cached 'new' stock units look up)
        if (null !== $stockUnit = $this->unitResolver->findLinkable($supplierOrderItem)) {
            $stockUnit->setSupplierOrderItem($supplierOrderItem);
        } else {
            $stockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);
        }

        $stockUnit
            ->setSupplierOrderItem($supplierOrderItem)
            ->setEstimatedDateOfArrival($supplierOrderItem->getOrder()->getEstimatedDateOfArrival());

        $this->updatePrice($stockUnit);

        $this->stockUnitUpdater->updateOrdered($stockUnit, $supplierOrderItem->getQuantity(), false);

        /*// We want the sold quantity to be equal to the ordered quantity.
        // We don't care about shipped quantity as 'new' stock units can't be shipped.
        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getOrderedQuantity() + $stockUnit->getAdjustedQuantity();
        if (0 >= $overflow) {
            $this->persistStockUnit($stockUnit);

            return;
        }

        // TODO What about pending stock units for the same sale item ?

        // New 'unlinked' stock unit for the sold quantity overflow
        $newStockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);
        $this->persistenceHelper->persistAndRecompute($newStockUnit);

        $overflow -= $this->moveAssignments($stockUnit, $newStockUnit, $overflow);

        if (0 < $overflow) {
            throw new StockLogicException("Failed to dispatch assignments.");
        }*/
    }

    /**
     * @inheritdoc
     */
    public function applyItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        // Supplier order item has been previously linked to a stock unit.
        $stockUnit = $supplierOrderItem->getStockUnit();

        $changed = false;

        // Update net price if needed
        if ($this->persistenceHelper->isChanged($supplierOrderItem, 'netPrice')) {
            if ($this->updatePrice($stockUnit)) {
                $this->persistenceHelper->persistAndRecompute($stockUnit, false);
                $changed = true;
            }
        }

        // Update ordered quantity if needed
        if ($this->persistenceHelper->isChanged($supplierOrderItem, 'quantity')) {
            $cs = $this->persistenceHelper->getChangeSet($supplierOrderItem, 'quantity');
            if (0 != $cs[1] - $cs[0]) { // TODO Use packaging format
                $this->stockUnitUpdater->updateOrdered($stockUnit, $supplierOrderItem->getQuantity(), false);
                $changed = true;
            }
        }

        return $changed;

        /*// Sync the ordered quantity
        $stockUnit->setOrderedQuantity($supplierOrderItem->getQuantity());

        // TODO update stock unit's net price if changed ?

        if ($stockUnit->getOrderedQuantity() < $stockUnit->getReceivedQuantity()) {
            throw new StockLogicException("Stock unit's ordered quantity can't be lower than received quantity.");
        }

        // We don't care about shipped quantities because of the 'ordered > received > shipped' rule.
        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getOrderedQuantity() + $stockUnit->getAdjustedQuantity();
        // Abort if no overflow
        if (0 == $overflow) {
            $this->persistStockUnit($stockUnit);

            return;
        }

        // Negative case : too much sold quantity
        if (0 < $overflow) {
            // Try to move sold overflow to other pending/ready stock units
            $targetStockUnits = $this->unitResolver->findPendingOrReady($supplierOrderItem);
            foreach ($targetStockUnits as $targetStockUnit) {
                // Skip the stock unit we're applying
                if ($targetStockUnit === $stockUnit) {
                    continue;
                }

                $overflow -= $this->moveAssignments($stockUnit, $targetStockUnit, $overflow);

                if (0 == $overflow) {
                    break; // We're done dispatching sold quantity
                }
            }

            // Move sold overflow to a new stock unit
            if (0 < $overflow) {
                // New 'unlinked' stock unit for the sold quantity overflow
                $newStockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);

                $overflow -= $this->moveAssignments($stockUnit, $newStockUnit, $overflow);
            }

            if (0 != $overflow) {
                throw new StockLogicException("Failed to apply supplier order item.");
            }

            return;
        }

        // Positive case : not enough sold quantity
        if (null !== $sourceUnit = $this->unitResolver->findLinkable($supplierOrderItem)) {
            $this->moveAssignments($sourceUnit, $stockUnit, -$overflow);
        }

        // Overflow may remain here, as we won't always get a source unit*/
    }

    /**
     * @inheritdoc
     */
    public function unlinkItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        if (null === $stockUnit = $supplierOrderItem->getStockUnit()) {
            return;
        }

        // Cache the stock unit
        // TODO Why ?
        //$this->unitResolver->getStockUnitCache()->add($stockUnit);

        // Unlink stock unit by setting supplier order item to null and ordered quantity to zero
        $stockUnit
            ->setSupplierOrderItem(null)
            ->setNetPrice(null)
            ->setEstimatedDateOfArrival(null);

        $this->stockUnitUpdater->updateOrdered($stockUnit, 0, false);

        /*if ($stockUnit->isEmpty()) {
            // Stock has no assignment
            // Remove the stock unit without scheduling event
            $this->persistenceHelper->remove($stockUnit, true);

            return;
        }

        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getAdjustedQuantity();

        if (0 < $overflow) {
            // Try to move assignments to not closed stock units
            // TODO stock unit sort/priority
            $targetStockUnits = $this->unitResolver->findPendingOrReady($supplierOrderItem);
            foreach ($targetStockUnits as $targetStockUnit) {
                // Skip the stock unit we're unlinking
                if ($targetStockUnit === $stockUnit) {
                    continue;
                }

                $overflow -= $this->moveAssignments($stockUnit, $targetStockUnit, $overflow);

                if (0 == $overflow) {
                    break; // We're done with re-assignment
                }
            }

            if (0 < $overflow) {
                // Try to merge assignments with a linkable stock unit's assignments
                $targetStockUnit = $this->unitResolver->findLinkable($supplierOrderItem);
                if (null !== $targetStockUnit && $stockUnit !== $targetStockUnit) {
                    $this->moveAssignments($stockUnit, $targetStockUnit, $overflow);
                }
            }
        }

        $this->persistStockUnit($stockUnit);*/
    }

    /**
     * Updates the stock unit price.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return bool Whether or not the net price has been updated.
     *
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    private function updatePrice(StockUnitInterface $stockUnit)
    {
        $price = null;

        if (null !== $item = $stockUnit->getSupplierOrderItem()) {
            if (null === $order = $item->getOrder()) {
                throw new StockLogicException("Supplier order item's order must be set at this point.");
            }

            $currency = $order->getCurrency()->getCode();
            $date = $order->getPaymentDate();
            if ($date > new \DateTime()) {
                $date = null;
            }

            $price = $this->currencyConverter->convert($item->getNetPrice(), $currency, null, $date);
        }

        if (0 !== Money::compare($stockUnit->getNetPrice(), $price, $this->currencyConverter->getDefaultCurrency())) {
            $stockUnit->setNetPrice($price);

            return true;
        }

        return false;
    }
}

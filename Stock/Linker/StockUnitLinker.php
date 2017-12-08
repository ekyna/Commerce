<?php

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
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
    private $persistenceHelper;

    /**
     * @var StockUnitResolverInterface
     */
    private $unitResolver;

    /**
     * @var StockUnitStateResolverInterface
     */
    private $stateResolver;

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface      $persistenceHelper
     * @param StockUnitResolverInterface      $unitResolver
     * @param StockUnitStateResolverInterface $stateResolver
     * @param SaleFactoryInterface            $saleFactory
     * @param CurrencyConverterInterface      $currencyConverter
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitResolverInterface $unitResolver,
        StockUnitStateResolverInterface $stateResolver,
        SaleFactoryInterface $saleFactory,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->unitResolver = $unitResolver;
        $this->stateResolver = $stateResolver;
        $this->saleFactory = $saleFactory;
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
            ->setOrderedQuantity($supplierOrderItem->getQuantity())
            ->setEstimatedDateOfArrival($supplierOrderItem->getOrder()->getEstimatedDateOfArrival());

        $this->updatePrice($stockUnit);

        // We want the sold quantity to be equal to the ordered quantity.
        // We don't care about shipped quantity as 'new' stock units can't be shipped.
        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getOrderedQuantity();
        if (0 >= $overflow) {
            $this->persistStockUnit($stockUnit);

            return;
        }

        // TODO What about pending stock units for the same sale item ?

        // New 'unlinked' stock unit for the sold quantity overflow
        $newStockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);

        $overflow -= $this->moveAssignments($stockUnit, $newStockUnit, $overflow);

        if (0 < $overflow) {
            throw new LogicException("Failed to dispatch assignments.");
        }
    }

    /**
     * @inheritdoc
     */
    public function applyItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        if (!$this->persistenceHelper->isChanged($supplierOrderItem, 'quantity')) {
            return;
        }

        $cs = $this->persistenceHelper->getChangeSet($supplierOrderItem, 'quantity');
        if (0 == $cs[1] - $cs[0]) {
            return;
        }

        // Supplier order item has been previously linked to a stock unit.
        $stockUnit = $supplierOrderItem->getStockUnit();
        // Sync the ordered quantity
        $stockUnit->setOrderedQuantity($supplierOrderItem->getQuantity());

        // TODO update stock unit's net price if changed ?

        if ($stockUnit->getOrderedQuantity() < $stockUnit->getReceivedQuantity()) {
            throw new LogicException("Stock unit's ordered quantity can't be lower than received quantity.");
        }

        // We don't care about shipped quantities because of the 'ordered > received > shipped' rule.
        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getOrderedQuantity();
        // Assignments are sorted from the most recent to the most ancient
        $assignments = $this->sortAssignments($stockUnit->getStockAssignments()->toArray());
        // Abort if no overflow
        if (empty($assignments) || 0 == $overflow) {
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
                throw new LogicException("Failed to apply supplier order item.");
            }

            return;
        }

        // Positive case : not enough sold quantity
        if (null !== $sourceUnit = $this->unitResolver->findLinkable($supplierOrderItem)) {
            $this->moveAssignments($sourceUnit, $stockUnit, -$overflow);
        }

        // Overflow may remain here, as we won't always get a source unit
    }

    /**
     * @inheritdoc
     */
    public function unlinkItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        if (null === $stockUnit = $supplierOrderItem->getStockUnit()) {
            return;
        }

        if (0 < $stockUnit->getReceivedQuantity() || 0 < $stockUnit->getShippedQuantity()) {
            throw new LogicException("Can't unlink supplier order item as it has been partially or fully received or shipped.");
        }

        // Cache the stock unit
        $this->unitResolver->getStockUnitCache()->add($stockUnit);

        // Unlink stock unit by setting supplier order item to null and ordered quantity to zero
        $stockUnit
            ->setSupplierOrderItem(null)
            ->setOrderedQuantity(0)
            ->setNetPrice(null)
            ->setEstimatedDateOfArrival(null);

        if (0 == $soldQty = $stockUnit->getSoldQuantity()) {
            // Stock has no assignment
            // Remove the stock unit without scheduling event
            $this->persistenceHelper->remove($stockUnit, true);

            return;
        }

        // Try to move assignments to not closed stock units
        // TODO stock unit sort/priority
        $targetStockUnits = $this->unitResolver->findPendingOrReady($supplierOrderItem);
        foreach ($targetStockUnits as $targetStockUnit) {
            // Skip the stock unit we're unlinking
            if ($targetStockUnit === $stockUnit) {
                continue;
            }

            $soldQty -= $this->moveAssignments($stockUnit, $targetStockUnit, $soldQty);

            if (0 == $soldQty) {
                break; // We're done with re-assignment
            }
        }

        if (0 < $soldQty) {
            // Try to merge assignments with a linkable stock unit's assignments
            $targetStockUnit = $this->unitResolver->findLinkable($supplierOrderItem);
            if (null !== $targetStockUnit && $stockUnit !== $targetStockUnit) {
                $this->moveAssignments($stockUnit, $targetStockUnit, $soldQty);
            }
        }

        $this->persistStockUnit($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updatePrice(StockUnitInterface $stockUnit)
    {
        $price = null;

        if (null !== $item = $stockUnit->getSupplierOrderItem()) {
            if (null === $order = $item->getOrder()) {
                throw new LogicException("Supplier order item's order must be set at this point.");
            }

            $currency = $order->getCurrency()->getCode();
            $date = $order->getPaymentDate();
            if ($date > new \DateTime()) {
                $date = null;
            }

            $price = $this->currencyConverter->convert($item->getNetPrice(), $currency, null, $date);
        }

        // TODO Use Money::compare() ?
        if ($stockUnit->getNetPrice() !== $price) {
            $stockUnit->setNetPrice($price);

            $this->persistStockUnit($stockUnit);
        }
    }

    /**
     * Moves (or splits) assignments from the source stock unit
     * to the target stock unit for the given quantity.
     *
     * @param StockUnitInterface $sourceUnit
     * @param StockUnitInterface $targetUnit
     * @param float              $quantity
     *
     * @return float The quantity indeed moved
     *
     * @throws StockLogicException
     */
    private function moveAssignments(StockUnitInterface $sourceUnit, StockUnitInterface $targetUnit, $quantity)
    {
        if (0 >= $quantity) {
            throw new StockLogicException("Quantity must be greater than zero.");
        }

        $moved = 0;

        // If the target stock unit is linked to a supplier order item,
        // don't create overflow on it.
        if (null !== $targetUnit->getSupplierOrderItem()) {
            $available = $targetUnit->getOrderedQuantity() - $targetUnit->getSoldQuantity();
            if (0 >= $available) {
                // Abort because sold quantity would become greater than ordered
                return $moved;
            }

            // Don't move more than available
            if ($quantity > $available) {
                $quantity = $available;
            }
        }


        $sourceAssignments = $this->sortAssignments($sourceUnit->getStockAssignments()->toArray());
        $targetAssignments = $targetUnit->getStockAssignments();

        foreach ($sourceAssignments as $sourceAssignment) {
            // Split assignment
            $saleItem = $sourceAssignment->getSaleItem();

            // Look for a target assignment with the same sale item
            $targetAssignment = null;
            foreach ($targetAssignments as $ta) {
                if ($ta->getSaleItem() === $saleItem) {
                    $targetAssignment = $ta;
                    break;
                }
            }

            // If no target assignment to merge into, move assignment
            if (null === $targetAssignment && $quantity >= $sourceAssignment->getSoldQuantity()) {
                $delta = $sourceAssignment->getSoldQuantity();
                $sourceAssignment->setStockUnit($targetUnit);
                $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $delta);
                $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $delta);

                $this->persistAssignment($sourceAssignment);

                $moved += $delta;
                $quantity -= $delta;
                if (0 == $quantity) {
                    break;
                }

                continue;
            }

            // If not found, create a new assignment
            if (null === $targetAssignment) {
                $targetAssignment = $this->saleFactory->createStockAssignmentForItem($saleItem);
                $targetAssignment
                    ->setSaleItem($saleItem)
                    ->setStockUnit($targetUnit);
            }

            // Add quantity to target assignment and unit
            $targetAssignment->setSoldQuantity($targetAssignment->getSoldQuantity() + $quantity);
            $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $quantity);

            $this->persistAssignment($targetAssignment);

            // Remove quantity from source unit and assignment
            $sourceAssignment->setSoldQuantity($sourceAssignment->getSoldQuantity() - $quantity);
            $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $quantity);

            $this->persistAssignment($sourceAssignment);

            $moved += $quantity;

            break;
        }

        $this->persistStockUnit($targetUnit);
        $this->persistStockUnit($sourceUnit);

        return $moved;
    }

    /**
     * Persists (or removes) the stock unit.
     *
     * @param StockUnitInterface $stockUnit
     */
    private function persistStockUnit(StockUnitInterface $stockUnit)
    {
        // TODO add/remove from/to stock unit cache ?

        // If empty, remove without scheduling event
        if ($stockUnit->isEmpty()) {
            // TODO Test if assignments is empty too ?
            $stockUnit->setSupplierOrderItem(null);
            $this->persistenceHelper->remove($stockUnit, true);

            return;
        }

        // Resolve the target stock unit's state
        // TODO Remove as it will be done by the stock unit listener (adapt test).
        $this->stateResolver->resolve($stockUnit);

        // Persist without scheduling event
        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }

    /**
     * Persists (or removes) the stock assignment.
     *
     * @param StockAssignmentInterface $assignment
     */
    private function persistAssignment(StockAssignmentInterface $assignment)
    {
        // Remove if empty
        if (0 == $assignment->getSoldQuantity()) {
            $assignment->setStockUnit(null);
            $this->persistenceHelper->remove($assignment, true);

            return;
        }

        // Persist without scheduling event
        $this->persistenceHelper->persistAndRecompute($assignment, true);
    }

    /**
     * Sort assignments from the most recent to the most ancient.
     *
     * @param StockAssignmentInterface[] $assignments
     *
     * @return StockAssignmentInterface[]
     */
    private function sortAssignments(array $assignments)
    {
        usort($assignments, function (StockAssignmentInterface $a, StockAssignmentInterface $b) {
            $aDate = $a->getSaleItem()->getSale()->getCreatedAt();
            $bDate = $b->getSaleItem()->getSale()->getCreatedAt();

            if ($aDate == $bDate) {
                return 0;
            }

            return $aDate < $bDate ? 1 : -1;
        });

        return $assignments;
    }
}

<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Logger\StockLoggerInterface;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;

/**
 * Class StockPrioritizer
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockPrioritizer implements StockPrioritizerInterface
{
    /**
     * @var StockUnitResolverInterface
     */
    private $unitResolver;

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var StockLoggerInterface
     */
    private $logger;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface $unitResolver
     * @param SaleFactoryInterface       $saleFactory
     * @param EntityManagerInterface     $manager
     * @param StockLoggerInterface $logger
     */
    public function __construct(
        StockUnitResolverInterface $unitResolver,
        SaleFactoryInterface $saleFactory,
        EntityManagerInterface $manager,
        StockLoggerInterface $logger
    ) {
        $this->unitResolver = $unitResolver;
        $this->saleFactory = $saleFactory;
        $this->manager = $manager;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function canPrioritizeSale(Common\SaleInterface $sale)
    {
        if ($sale->getState() === ShipmentStates::STATE_COMPLETED) {
            return false;
        }

        foreach ($sale->getItems() as $item) {
            if ($this->canPrioritizeSaleItem($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function canPrioritizeSaleItem(Common\SaleItemInterface $item)
    {
        foreach ($item->getChildren() as $child) {
            if ($this->canPrioritizeSaleItem($child)) {
                return true;
            }
        }

        if (!$item instanceof Stock\StockAssignmentsInterface) {
            return false;
        }

        foreach ($item->getStockAssignments() as $assignment) {
            if (!$assignment->isFullyShipped() && !$assignment->isFullyShippable()) {
                return true;
            }
        }

        return false;
    }


    /**
     * @inheritdoc
     */
    public function prioritizeSale(Common\SaleInterface $sale)
    {
        $changed = false;

        foreach ($sale->getItems() as $item) {
            $changed |= $this->prioritizeSaleItem($item);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function prioritizeSaleItem(Common\SaleItemInterface $item)
    {
        $changed = false;

        foreach ($item->getChildren() as $child) {
            $changed |= $this->prioritizeSaleItem($child);
        }

        if (!$item instanceof Stock\StockAssignmentsInterface) {
            return $changed;
        }

        foreach ($item->getStockAssignments() as $assignment) {
            $changed |= $this->prioritizeAssignment($assignment);
        }

        return $changed;
    }

    /**
     * Prioritize the stock assignment.
     *
     * @param Stock\StockAssignmentInterface $assignment
     *
     * @return bool Whether the assignment has been prioritized.
     */
    private function prioritizeAssignment(Stock\StockAssignmentInterface $assignment)
    {
        if ($assignment->isFullyShipped() || $assignment->isFullyShippable()) {
            return false;
        }

        // Get the non shippable quantity
        if (0 >= $quantity = $assignment->getSoldQuantity() - $assignment->getShippableQuantity()) {
            return false;
        }

        // Options are:
        // - Splitting non shippable quantity to other stock unit(s)
        // - Moving the whole assignment to other stock unit(s) (TODO)
        // - Moving other assignment(s) to other stock unit(s) (TODO)

        $changed = false;

        $helper = new PrioritizeHelper($this->unitResolver);

        $sourceUnit = $assignment->getStockUnit();

        while (null !== $candidate = $helper->getUnitCandidate($assignment, $quantity)) {
            $targetUnit = $candidate->unit;

            // If not enough reservable quantity
            if (0 < $diff = $quantity - $targetUnit->getReservableQuantity()) {
                // Use combination to release quantity
                $combination = $candidate->combination;
                foreach ($combination->map as $id => $qty) {
                    if (null === $a = $candidate->getAssignmentById($id)) {
                        throw new StockLogicException("Assignment not found.");
                    }

                    // Move assignment to the source unit
                    $this->moveAssignment($a, $sourceUnit, min($qty, $quantity));
                }
            }

            // Move assignment to the target unit.
            $delta = min($quantity, $targetUnit->getReservableQuantity());
            $this->moveAssignment($assignment, $targetUnit, $delta);

            // TODO Validate units ?

            $changed = true;

            $quantity -= $delta;
            if (0 >= $quantity) {
                break;
            }
        }

        return $changed;
    }

    /**
     * Move the given assignment to the given unit for the given sold quantity.
     *
     * @param Stock\StockAssignmentInterface $assignment
     * @param Stock\StockUnitInterface       $targetUnit
     * @param float                          $quantity
     *
     * @return float The quantity moved
     */
    private function moveAssignment(
        Stock\StockAssignmentInterface $assignment,
        Stock\StockUnitInterface $targetUnit,
        $quantity
    ) {
        // Don't move shipped quantity
        if ($quantity > $assignment->getSoldQuantity() - $assignment->getShippedQuantity()) {
            throw new StockLogicException("Can't move the given quantity.");
        }

        $sourceUnit = $assignment->getStockUnit();
        $saleItem = $assignment->getSaleItem();

        // Debit source unit's sold quantity
        $this->logger->unitSold($sourceUnit, -$quantity);
        $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $quantity);
        $this->manager->persist($sourceUnit);

        // Credit target unit
        $this->logger->unitSold($targetUnit, $quantity);
        $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $quantity);
        $this->manager->persist($targetUnit);

        // Merge assignment lookup
        $merge = null;
        foreach ($targetUnit->getStockAssignments() as $m) {
            if ($m->getSaleItem() === $saleItem) {
                $merge = $m;
                break;
            }
        }

        if ($quantity == $assignment->getSoldQuantity()) {
            // Move assignment
            if (null !== $merge) {
                $this->logger->assignmentSold($merge, $quantity);
                $merge->setSoldQuantity($merge->getSoldQuantity() + $quantity);

                $this->manager->persist($merge);

                $this->logger->assignmentSold($assignment, 0, false); // TODO log removal ?
                $this->manager->remove($assignment);
            } else {
                $this->logger->assignmentUnit($assignment, $targetUnit);
                $assignment->setStockUnit($targetUnit);
                $this->manager->persist($assignment);
            }
        } else {
            // Split assignment
            $this->logger->assignmentSold($assignment, -$quantity);
            $assignment->setSoldQuantity($assignment->getSoldQuantity() - $quantity);
            $this->manager->persist($assignment);

            if (null !== $merge) {
                $this->logger->assignmentSold($merge, $quantity);
                $merge->setSoldQuantity($merge->getSoldQuantity() + $quantity);
                $this->manager->persist($merge);
            } else {
                // Create new assignment
                $create = $this->saleFactory->createStockAssignmentForItem($saleItem);
                $this->logger->assignmentSold($create, $quantity, false);
                $create
                    ->setSoldQuantity($quantity)
                    ->setSaleItem($saleItem)
                    ->setStockUnit($targetUnit);

                $this->manager->persist($create);
            }
        }

        return $quantity;
    }
}
<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Commerce\Stock\Logger\StockLoggerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
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
    protected $unitResolver;

    /**
     * @var StockUnitAssignerInterface
     */
    protected $unitAssigner;

    /**
     * @var StockUnitManagerInterface
     */
    protected $unitManager;

    /**
     * @var StockAssignmentManagerInterface
     */
    protected $assignmentManager;

    /**
     * @var StockLoggerInterface
     */
    protected $logger;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface      $unitResolver
     * @param StockUnitAssignerInterface      $unitAssigner
     * @param StockUnitManagerInterface       $unitManager
     * @param StockAssignmentManagerInterface $assignmentManager
     * @param StockLoggerInterface            $logger
     */
    public function __construct(
        StockUnitResolverInterface $unitResolver,
        StockUnitAssignerInterface $unitAssigner,
        StockUnitManagerInterface $unitManager,
        StockAssignmentManagerInterface $assignmentManager,
        StockLoggerInterface $logger
    ) {
        $this->unitResolver = $unitResolver;
        $this->unitAssigner = $unitAssigner;
        $this->unitManager = $unitManager;
        $this->assignmentManager = $assignmentManager;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function canPrioritizeSale(Common\SaleInterface $sale): bool
    {
        if (!$this->checkSale($sale)) {
            return false;
        }

        foreach ($sale->getItems() as $item) {
            if ($this->canPrioritizeSaleItem($item, false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function canPrioritizeSaleItem(Common\SaleItemInterface $item, bool $checkSale = true): bool
    {
        if ($checkSale && !$this->checkSale($item->getSale())) {
            return false;
        }

        foreach ($item->getChildren() as $child) {
            if ($this->canPrioritizeSaleItem($child, false)) {
                return true;
            }
        }

        if (!$item instanceof Stock\StockAssignmentsInterface) {
            return false;
        }

        $assignments = $item->getStockAssignments();

        if (0 === $assignments->count()) {
            return $this->unitAssigner->supportsAssignment($item);
        }

        foreach ($assignments as $assignment) {
            if (!$assignment->isFullyShipped() && !$assignment->isFullyShippable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function prioritizeSale(Common\SaleInterface $sale): bool
    {
        if (!$this->checkSale($sale)) {
            return false;
        }

        $changed = false;

        foreach ($sale->getItems() as $item) {
            $changed |= $this->prioritizeSaleItem($item, null, false);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function prioritizeSaleItem(
        Common\SaleItemInterface $item,
        float $quantity = null,
        bool $checkSale = true
    ): bool {
        if ($checkSale && !$this->checkSale($item->getSale())) {
            return false;
        }

        $changed = false;

        foreach ($item->getChildren() as $child) {
            $changed |= $this->prioritizeSaleItem($child, $quantity ? $quantity * $child->getQuantity() : null, false);
        }

        if (!$item instanceof Stock\StockAssignmentsInterface) {
            return $changed;
        }

        $assignments = $item->getStockAssignments();

        if (0 === $assignments->count()) {
            if ($this->unitAssigner->supportsAssignment($item)) {
                $this->unitAssigner->assignSaleItem($item);

                $changed = true;
            }

            return $changed;
        }

        foreach ($item->getStockAssignments() as $assignment) {
            $changed |= $this->prioritizeAssignment($assignment, $quantity);
        }

        return $changed;
    }

    /**
     * Checks whether the sale can be prioritized.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    protected function checkSale(Common\SaleInterface $sale)
    {
        if (!$sale instanceof OrderInterface) {
            return false;
        }

        if (!OrderStates::isStockableState($sale->getState())) {
            return false;
        }

        if ($sale->getShipmentState() === ShipmentStates::STATE_COMPLETED) {
            return false;
        }

        return true;
    }

    /**
     * Prioritize the stock assignment.
     *
     * @param Stock\StockAssignmentInterface $assignment
     * @param float                          $quantity
     *
     * @return bool Whether the assignment has been prioritized.
     */
    protected function prioritizeAssignment(Stock\StockAssignmentInterface $assignment, float $quantity = null)
    {
        if ($assignment->isFullyShipped() || $assignment->isFullyShippable()) {
            return false;
        }

        if (is_null($quantity)) {
            // Get the non shippable quantity
            $quantity = $assignment->getSoldQuantity() - $assignment->getShippableQuantity();
        }

        if (0 >= $quantity) {
            return false;
        }

        // Options are:
        // - Splitting non shippable quantity to other stock unit(s)
        // - Moving the whole assignment to other stock unit(s) (TODO)
        // - Moving other assignment(s) to other stock unit(s) (TODO)

        $changed = false;

        $helper = new PrioritizeHelper($this->unitResolver);

        $sourceUnit = $assignment->getStockUnit();

        while ($candidate = $helper->getUnitCandidate($assignment, $quantity)) {
            $targetUnit = $candidate->unit;

            $diff = $quantity - $targetUnit->getReservableQuantity();

            // If not enough reservable quantity, release as much as needed/possible
            if (0 < $diff && $combination = $candidate->getCombination($diff)) {
                // Use combination to release quantity
                foreach ($combination->map as $id => $qty) {
                    if (null === $a = $candidate->getAssignmentById($id)) {
                        throw new StockLogicException("Assignment not found.");
                    }

                    // Move assignment to the source unit
                    $diff -= $this->moveAssignment($a, $sourceUnit, min($qty, $diff));

                    if (0 >= $diff) {
                        break;
                    }
                }
            }

            // Move assignment to the target unit using reservable quantity first.
            $delta = min($quantity, $targetUnit->getReservableQuantity());
            $quantity -= $this->moveAssignment($assignment, $targetUnit, $delta);

            // TODO Validate units ?

            $changed = true;
            if (0 >= $quantity || $assignment->isFullyShippable()) {
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
    protected function moveAssignment(
        Stock\StockAssignmentInterface $assignment,
        Stock\StockUnitInterface $targetUnit,
        $quantity
    ) {
        /**
         * TODO Refactor with:
         * @see \Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcher::moveAssignments()
         */

        // Don't move shipped quantity
        $quantity = min($quantity, $assignment->getSoldQuantity() - $assignment->getShippedQuantity());
        if (0 >= $quantity) { // TODO Packaging format
            return 0;
        }

        $sourceUnit = $assignment->getStockUnit();
        $saleItem = $assignment->getSaleItem();

        // Debit source unit's sold quantity
        $this->logger->unitSold($sourceUnit, -$quantity);
        $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $quantity);
        $this->unitManager->persistOrRemove($sourceUnit); // TODO Without event scheduling ?

        // Credit target unit
        $this->logger->unitSold($targetUnit, $quantity);
        $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $quantity);
        $this->unitManager->persistOrRemove($targetUnit); // TODO Without event scheduling ?

        // Merge assignment lookup
        $merge = null;
        foreach ($targetUnit->getStockAssignments() as $m) {
            if ($m->getSaleItem() === $saleItem) {
                $merge = $m;
                break;
            }
        }

        if ($quantity == $assignment->getSoldQuantity()) {
            if (null !== $merge) {
                // Credit quantity to mergeable assignment
                $this->logger->assignmentSold($merge, $quantity);
                $merge->setSoldQuantity($merge->getSoldQuantity() + $quantity);
                $this->assignmentManager->persist($merge);

                // Debit quantity from source assignment
                $this->logger->assignmentSold($assignment, 0, false); // TODO log removal ?
                $assignment->setSoldQuantity(0);
                $this->assignmentManager->remove($assignment);
            } else {
                // Move source assignment to target unit
                $this->logger->assignmentUnit($assignment, $targetUnit);
                $assignment->setStockUnit($targetUnit);
                $this->assignmentManager->persist($assignment);
            }
        } else {
            // Debit quantity from source assignment
            $this->logger->assignmentSold($assignment, -$quantity);
            $assignment->setSoldQuantity($assignment->getSoldQuantity() - $quantity);
            $this->assignmentManager->persist($assignment);

            if (null !== $merge) {
                // Credit quantity to mergeable assignment
                $this->logger->assignmentSold($merge, $quantity);
                $merge->setSoldQuantity($merge->getSoldQuantity() + $quantity);
                $this->assignmentManager->persist($merge);
            } else {
                // Credit quantity to new assignment
                $create = $this->assignmentManager->create($saleItem, $targetUnit);
                $this->logger->assignmentSold($create, $quantity, false);
                $create
                    ->setSoldQuantity($quantity)
                    ->setSaleItem($saleItem)
                    ->setStockUnit($targetUnit);

                $this->assignmentManager->persist($create);
            }
        }

        return $quantity;
    }
}

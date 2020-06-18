<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcherInterface;
use Ekyna\Component\Commerce\Stock\Logger\StockLoggerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface as Assignment;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
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
     * @var StockUnitCacheInterface
     */
    protected $unitCache;

    /**
     * @var StockAssignmentManagerInterface
     */
    protected $assignmentManager;

    /**
     * @var StockAssignmentDispatcherInterface
     */
    protected $assignmentDispatcher;

    /**
     * @var StockLoggerInterface
     */
    protected $logger;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface         $unitResolver
     * @param StockUnitAssignerInterface         $unitAssigner
     * @param StockUnitManagerInterface          $unitManager
     * @param StockUnitCacheInterface            $unitCache
     * @param StockAssignmentManagerInterface    $assignmentManager
     * @param StockAssignmentDispatcherInterface $assignmentDispatcher
     * @param StockLoggerInterface               $logger
     */
    public function __construct(
        StockUnitResolverInterface $unitResolver,
        StockUnitAssignerInterface $unitAssigner,
        StockUnitManagerInterface $unitManager,
        StockUnitCacheInterface $unitCache,
        StockAssignmentManagerInterface $assignmentManager,
        StockAssignmentDispatcherInterface $assignmentDispatcher,
        StockLoggerInterface $logger
    ) {
        $this->unitResolver         = $unitResolver;
        $this->unitAssigner         = $unitAssigner;
        $this->unitManager          = $unitManager;
        $this->unitCache            = $unitCache;
        $this->assignmentManager    = $assignmentManager;
        $this->assignmentDispatcher = $assignmentDispatcher;
        $this->logger               = $logger;
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

        if (!$item instanceof StockAssignmentsInterface) {
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

        if (!$item instanceof StockAssignmentsInterface) {
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
     * @param Assignment $assignment
     * @param float      $quantity
     *
     * @return bool Whether the assignment has been prioritized.
     */
    protected function prioritizeAssignment(Assignment $assignment, float $quantity = null)
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

        $helper = new PrioritizeHelper($this->unitResolver, $this->unitCache);

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
                    $diff -= $this->assignmentDispatcher->moveAssignment($a, $sourceUnit, min($qty, $diff));

                    $this->unitManager->persistOrRemove($targetUnit);
                    $this->unitManager->persistOrRemove($sourceUnit);

                    if (0 >= $diff) {
                        break;
                    }
                }
            }

            // Move assignment to the target unit using reservable quantity first.
            $delta    = min($quantity, $targetUnit->getReservableQuantity());
            $quantity -= $this->assignmentDispatcher->moveAssignment($assignment, $targetUnit, $delta);

            $this->unitManager->persistOrRemove($sourceUnit);
            $this->unitManager->persistOrRemove($targetUnit);

            $changed = true;
            if (0 >= $quantity || $assignment->isFullyShippable()) {
                break;
            }
        }

        return $changed;
    }
}

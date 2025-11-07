<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcherInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface as Assignment;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;

use function is_null;
use function min;

/**
 * Class AbstractPrioritizer
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPrioritizer
{
    public function __construct(
        protected readonly StockUnitResolverInterface         $unitResolver,
        protected readonly StockUnitAssignerInterface         $unitAssigner,
        protected readonly StockUnitManagerInterface          $unitManager,
        protected readonly StockUnitCacheInterface            $unitCache,
        protected readonly StockAssignmentDispatcherInterface $assignmentDispatcher,
    ) {
    }

    /**
     * Prioritize the stock assignment.
     *
     * @return bool Whether the assignment has been prioritized.
     */
    protected function prioritizeAssignment(
        Assignment $assignment,
        ?Decimal   $quantity,
        bool       $sameSale = false,
    ): bool {
        if ($assignment->isFullyShipped() || $assignment->isFullyShippable()) {
            return false;
        }

        if (is_null($quantity)) {
            // Get the non-shippable quantity
            $quantity = $assignment->getSoldQuantity()->sub($assignment->getShippableQuantity());
        }

        if (0 >= $quantity) {
            return false;
        }

        // Options are:
        // - Splitting non shippable quantity to other stock unit(s)
        // - Moving the whole assignment to other stock unit(s) (TODO)
        // - Moving other assignment(s) to other stock unit(s) (TODO)

        $changed = false;

        $helper = new PrioritizeUnitResolver($this->unitResolver, $this->unitCache, $sameSale);

        $sourceUnit = $assignment->getStockUnit();

        while ($candidate = $helper->getUnitCandidate($assignment, $quantity)) {
            $targetUnit = $candidate->unit;

            $diff = $quantity - $targetUnit->getReservableQuantity();

            // If not enough reservable quantity, release as much as needed/possible
            if (0 < $diff && (null !== $combination = $candidate->getCombination($diff))) {
                // Use combination to release quantity
                foreach ($combination->map as $id => $qty) {
                    if (null === $a = $candidate->getAssignmentById($id)) {
                        throw new StockLogicException('Assignment not found.');
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
            $delta = min($quantity, $targetUnit->getReservableQuantity());
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

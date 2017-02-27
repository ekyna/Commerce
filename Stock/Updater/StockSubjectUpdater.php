<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockSubjectUpdater
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectUpdater implements StockSubjectUpdaterInterface
{
    /**
     * @var StockUnitResolverInterface
     */
    protected $stockUnitResolver;

    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockUnitResolverInterface $stockUnitResolver
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitResolverInterface $stockUnitResolver
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUnitResolver = $stockUnitResolver;
    }

    /**
     * @inheritdoc
     */
    public function updateInStock(StockSubjectInterface $subject, $quantity = null)
    {
        $stock = 0;
        if (null !== $quantity) {
            // Relative update
            $stock = $subject->getInStock() + (float)$quantity;
        } else {
            // Resolve the in stock quantity
            // TODO What about reserved stocks ?
            $stockUnits = $this->findAvailableOrPendingStockUnits($subject);
            foreach ($stockUnits as $stockUnit) {
                $stock += $stockUnit->getInStockQuantity();
            }
        }

        if ($stock != $subject->getInStock()) { // TODO use packaging format (bccomp for float)
            $subject->setInStock($stock);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateOrderedStock(StockSubjectInterface $subject, $quantity = null)
    {
        $ordered = 0;
        if (null !== $quantity) {
            //  Relative update
            $ordered = $subject->getOrderedStock() + (float)$quantity;
        } else {
            // Resolve the ordered stock
            // TODO What about reserved stocks ?
            $stockUnits = $this->findAvailableOrPendingStockUnits($subject);
            foreach ($stockUnits as $stockUnit) {
                $ordered += $stockUnit->getOrderedQuantity();
            }
        }

        if ($ordered != $subject->getOrderedStock()) { // TODO use packaging format (bccomp for float)
            $subject->setOrderedStock($ordered);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateEstimatedDateOfArrival(StockSubjectInterface $subject, \DateTime $date = null)
    {
        $currentDate = $subject->getEstimatedDateOfArrival();

        // Abort if subject has in stock greater than zero or a zero ordered stock
        if ((0 < $subject->getInStock()) || (0 >= $subject->getOrderedStock())) {
            if (null !== $currentDate) {
                $subject->setEstimatedDateOfArrival(null);

                return true;
            }

            return false;
        }

        if (null !== $date) {
            // Relative update : cancel if the date is greater than current
            if ($currentDate && $date >= $currentDate) {
                $date = null;
            }
        } else {
            // Resolve the minimum estimated date of arrival
            $stockUnits = $this->findAvailableOrPendingStockUnits($subject);
            foreach ($stockUnits as $stockUnit) {
                $unitEDA = $stockUnit->getEstimatedDateOfArrival();

                if (null === $date || $date > $unitEDA) {
                    $date = $unitEDA;
                }
            }
        }

        // ETA must be greater than today's first second
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        if ($date && $today < $date) {
            if ($date != $currentDate) {
                $subject->setEstimatedDateOfArrival($date);

                return true;
            }
        } elseif (null !== $currentDate) {
            $subject->setEstimatedDateOfArrival(null);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateStockState(StockSubjectInterface $subject)
    {
        // If subject stock mode is 'disabled', do nothing.
        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return false;
        }

        // If subject stock mode is 'just in time', set state to 'available'.
        if ($subject->getStockMode() === StockSubjectModes::MODE_JUST_IN_TIME) {
            if ($subject->getStockState() != StockSubjectStates::STATE_IN_STOCK) {
                $subject->setStockState(StockSubjectStates::STATE_IN_STOCK);

                return true;
            }

            return false;
        }

        // Stock mode should be 'enabled'
        if ($subject->getStockMode() != StockSubjectModes::MODE_ENABLED) {
            throw new InvalidArgumentException("Unexpected stock mode.");
        }

        // Current subject stock state
        $currentState = $subject->getStockState();

        // TODO What about reserved stocks ?

        // "In stock" resolved state
        if (0 < $subject->getInStock()) {
            if ($currentState != StockSubjectStates::STATE_IN_STOCK) {
                $subject->setStockState(StockSubjectStates::STATE_IN_STOCK);

                return true;
            }

            return false;
        }

        // "Pre order" resolved state
        if ((0 < $subject->getOrderedStock()) && (null !== $subject->getEstimatedDateOfArrival())) {
            if ($currentState != StockSubjectStates::STATE_PRE_ORDER) {
                $subject->setStockState(StockSubjectStates::STATE_PRE_ORDER);

                return true;
            }

            return false;
        }

        // "Out of stock" resolved state
        if ($currentState != StockSubjectStates::STATE_OUT_OF_STOCK) {
            $subject->setStockState(StockSubjectStates::STATE_OUT_OF_STOCK);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function update(StockSubjectInterface $subject)
    {
        $changed = $this->updateInStock($subject);

        $changed = $this->updateOrderedStock($subject) || $changed;

        $changed = $this->updateEstimatedDateOfArrival($subject) || $changed;

        return $this->updateStockState($subject) || $changed;
    }


    /**
     * @inheritdoc
     */
    public function updateFromStockUnitChange(StockSubjectInterface $subject, StockUnitInterface $stockUnit)
    {
        // TODO prevent call while not in a persistence phase (flush).

        $cs = $this->persistenceHelper->getChangeSet($stockUnit);

        $changed = false;

        if (isset($cs['orderedQuantity'])) {
            // Resolve ordered quantity change
            if (0 != $orderedDelta = ((float)$cs['orderedQuantity'][1]) - ((float)$cs['orderedQuantity'][0])) {
                $changed = $this->updateOrderedStock($subject, $orderedDelta) || $changed;
            }
        }

        if (isset($cs['deliveredQuantity']) || isset($cs['shippedQuantity'])) {
            // Resolve delivered and shipped quantity changes
            $deliveredDelta = $deltaShipped = 0;
            if (isset($cs['deliveredQuantity'])) {
                if (0 != $deliveredDelta = ((float)$cs['deliveredQuantity'][1]) - ((float)$cs['deliveredQuantity'][0])) {
                    $changed = $this->updateOrderedStock($subject, -$deliveredDelta) || $changed;
                }
            }
            if (isset($cs['shippedQuantity'])) {
                $deltaShipped = ((float)$cs['shippedQuantity'][1]) - ((float)$cs['shippedQuantity'][0]);
            }

            // TODO really need tests T_T
            if (0 != $inStockDelta = $deliveredDelta - $deltaShipped) {
                $changed = $this->updateInStock($subject, $inStockDelta);
            }
        }

        if ($changed || isset($cs['estimatedDateOfArrival'])) {
            $date = $stockUnit->getState() !== StockUnitStates::STATE_CLOSED
                ? $stockUnit->getEstimatedDateOfArrival()
                : null;

            $changed = $this->updateEstimatedDateOfArrival($subject, $date) || $changed;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateFromStockUnitRemoval(StockSubjectInterface $subject, StockUnitInterface $stockUnit)
    {
        // TODO prevent call while not in a persistence phase (flush).

        $changed = false;

        // We don't care about delivered and shipped stocks because the
        // stock unit removal is prevented if those stocks are not null.

        // Update ordered quantity
        if (0 < $stockUnit->getOrderedQuantity()) {
            $changed = $this->updateOrderedStock($subject, -$stockUnit->getOrderedQuantity());
        }

        // Update the estimated date of arrival
        $changed = $this->updateEstimatedDateOfArrival($subject) || $changed;

        return $changed;
    }

    /**
     * Finds the available or pending stock units.
     *
     * @param StockSubjectInterface $subject
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    private function findAvailableOrPendingStockUnits(StockSubjectInterface $subject)
    {
        // Get the stock unit repository
        $repository = $this
            ->stockUnitResolver
            ->getRepositoryBySubject($subject);

        if (!$repository) {
            // Subject does not support stock unit management.
            return [];
        }

        return $repository->findAvailableOrPendingBySubject($subject);
    }
}

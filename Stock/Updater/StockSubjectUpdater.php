<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;

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
     * Constructor.
     *
     * @param StockUnitResolverInterface $stockUnitResolver
     */
    public function __construct(StockUnitResolverInterface $stockUnitResolver)
    {
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

        // EDA must be greater than today's first second
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

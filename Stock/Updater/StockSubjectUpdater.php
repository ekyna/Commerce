<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Model\StockModes;
use Ekyna\Component\Commerce\Stock\Model\StockStates;
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

        return $repository->findAvailableOrPendingStockUnitsBySubject($subject);
    }

    /**
     * 1. Updates the subject's "in stock" quantity.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateInStock(StockSubjectInterface $subject)
    {
        // Find subject stock units
        $stockUnits = $this->findAvailableOrPendingStockUnits($subject);

        $stock = 0;
        foreach ($stockUnits as $stockUnit) {
            $stock += $stockUnit->getInStockQuantity();
        }

        // Update subject stock
        if ($stock != $subject->getInStock()) { // TODO use packaging format (bccomp for float)
            $subject->setInStock($stock);

            return true;
        }

        return false;
    }

    /**
     * 2. Updates the subject's "ordered stock" quantity.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateOrderedStock(StockSubjectInterface $subject)
    {
        // Find subject stock units
        $stockUnits = $this->findAvailableOrPendingStockUnits($subject);

        $ordered = 0;
        foreach ($stockUnits as $stockUnit) {
            $ordered += $stockUnit->getOrderedQuantity();
        }

        // Update subject stock
        if ($ordered != $subject->getOrderedStock()) { // TODO use packaging format (bccomp for float)
            $subject->setOrderedStock($ordered);

            return true;
        }

        return false;
    }

    /**
     * 3. Updates the subject's estimated date of arrival date.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateEstimatedDateOfArrival(StockSubjectInterface $subject)
    {
        // Abort if subject has in stock greater than zero or a zero ordered stock
        if ((0 < $subject->getInStock()) || (0 >= $subject->getOrderedStock())) {
            if (null !== $subject->getEstimatedDateOfArrival()) {
                $subject->setEstimatedDateOfArrival(null);

                return true;
            }

            return false;
        }

        // Find subject stock units
        $stockUnits = $this->findAvailableOrPendingStockUnits($subject);

        $minEDA = $maxEDA = null;

        foreach ($stockUnits as $stockUnit) {
            $eda = $stockUnit->getEstimatedDateOfArrival();

            if (null === $minEDA || $minEDA > $eda) {
                $minEDA = $eda;
            }
            /*if (null === $maxEDA || $maxEDA < $eda) {
                $maxEDA = $eda;
            }*/
        }

        // Eda must be greater than today's first second
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        if ($today < $minEDA) {
            if ($minEDA != $subject->getEstimatedDateOfArrival()) {
                $subject->setEstimatedDateOfArrival($minEDA);
                return true;
            }
        } elseif (null !== $subject->getEstimatedDateOfArrival()) {
            $subject->setEstimatedDateOfArrival(null);

            return true;
        }

        return false;
    }

    /**
     * 4. Updates the subject's stock state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateStockState(StockSubjectInterface $subject)
    {
        // If subject stock mode is 'disabled', do nothing.
        if ($subject->getStockMode() === StockModes::MODE_DISABLED) {
            return false;
        }

        // If subject stock mode is 'just in time', set state to 'available'.
        if ($subject->getStockMode() === StockModes::MODE_JUST_IN_TIME) {
            if ($subject->getStockState() != StockStates::STATE_IN_STOCK) {
                $subject->setStockState(StockStates::STATE_IN_STOCK);

                return true;
            }

            return false;
        }

        // Stock mode should be 'enabled'
        if ($subject->getStockMode() != StockModes::MODE_ENABLED) {
            throw new InvalidArgumentException("Unexpected stock mode.");
        }

        // Current subject stock state
        $currentState = $subject->getStockState();

        // "In stock" resolved state
        if (0 < $subject->getInStock()) {
            if ($currentState != StockStates::STATE_IN_STOCK) {
                $subject->setStockState(StockStates::STATE_IN_STOCK);
                return true;
            }

            return false;
        }

        // "Pre order" resolved state
        if ((0 < $subject->getOrderedStock()) && (null !== $subject->getEstimatedDateOfArrival())) {
            if ($currentState != StockStates::STATE_PRE_ORDER) {
                $subject->setStockState(StockStates::STATE_PRE_ORDER);
                return true;
            }

            return false;
        }

        // "Out of stock" resolved state
        if ($currentState != StockStates::STATE_OUT_OF_STOCK) {
            $subject->setStockState(StockStates::STATE_OUT_OF_STOCK);
            return true;
        }

        return false;
    }

    /**
     * Updates the subject's stocks, eda and state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function update(StockSubjectInterface $subject)
    {
        $changed = $this->updateInStock($subject);

        $changed = $this->updateOrderedStock($subject) || $changed;

        $changed = $this->updateEstimatedDateOfArrival($subject) || $changed;

        return $this->updateStockState($subject) || $changed;
    }
}

<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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
     * Updates the subject's stock and state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function update(StockSubjectInterface $subject)
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

        // Get the stock unit repository
        $repository = $this
            ->stockUnitResolver
            ->getRepositoryBySubject($subject);
        if (null === $repository) {
            // Subject does not support stock unit management.
            return false;
        }

        // Find subject stock units
        $stockUnits = $repository->findAvailableOrPendingStockUnitsBySubject($subject);

        // Resolve 'available' and 'expected' stocks, and min/max 'estimated date of arrival'.
        $inStock = 0;
        $ordered = 0;
        $minEDA = $maxEDA = null;

        foreach ($stockUnits as $stockUnit) {
            $inStock += $stockUnit->getInStockQuantity();
            $ordered += $stockUnit->getOrderedQuantity();

            $eda = $stockUnit->getEstimatedDateOfArrival();

            if (null === $minEDA || $minEDA > $eda) {
                $minEDA = $eda;
            }
            if (null === $maxEDA || $maxEDA < $eda) {
                $maxEDA = $eda;
            }
        }

        // Track change
        $changed = false;

        // Update subject stock
        if ($inStock != $subject->getStock()) { // TODO use bccomp() (float)
            $subject->setStock($inStock);
            $changed = true;
        }

        // Current subject stock state
        $currentState = $subject->getStockState();

        // "In stock" resolved state
        if (0 < $inStock) {
            if ($currentState != StockStates::STATE_IN_STOCK) {
                $subject->setStockState(StockStates::STATE_IN_STOCK);
                $changed = true;
            }
            if (null != $subject->getEstimatedDateOfArrival()) {
                $subject->setEstimatedDateOfArrival(null);
                $changed = true;
            }
            return $changed;
        }

        // "Pre order" resolved state
        $tomorrow = new \DateTime('+1 day');
        $tomorrow->setTime(0, 0, 0);
        if (0 < $ordered && $tomorrow < $minEDA) {
            if ($minEDA != $subject->getEstimatedDateOfArrival()) {
                $subject->setEstimatedDateOfArrival($minEDA);
                $changed = true;
            }
            if ($currentState != StockStates::STATE_PRE_ORDER) {
                $subject->setStockState(StockStates::STATE_PRE_ORDER);
                $changed = true;
            }

            return $changed;
        }

        // "Out of stock" resolved state
        if ($currentState != StockStates::STATE_OUT_OF_STOCK) {
            $subject->setStockState(StockStates::STATE_OUT_OF_STOCK);
            $changed = true;
        }
        if (null != $subject->getEstimatedDateOfArrival()) {
            $subject->setEstimatedDateOfArrival(null);
            $changed = true;
        }

        return $changed;
    }
}

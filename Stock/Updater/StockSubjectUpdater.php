<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
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
        // TODO Check usages

        $stock = 0;
        if (null !== $quantity) {
            // Relative update
            $stock = $subject->getInStock() + (float)$quantity;
        } else {
            // Resolve the in stock quantity
            $stockUnits = $this->stockUnitResolver->findNotClosed($subject);
            foreach ($stockUnits as $stockUnit) {
                $stock += $stockUnit->getInStockQuantity(); // Delivered - Reserved
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
    public function updateVirtualStock(StockSubjectInterface $subject, $quantity = null)
    {
        $virtual = 0;
        if (null !== $quantity) {
            //  Relative update
            $virtual = $subject->getVirtualStock() + (float)$quantity;
        } else {
            // Resolve the virtual stock
            $stockUnits = $this->stockUnitResolver->findPendingOrReady($subject);
            foreach ($stockUnits as $stockUnit) {
                $virtual += $stockUnit->getVirtualStockQuantity(); // Ordered - Reserved
            }
        }

        if ($virtual != $subject->getVirtualStock()) { // TODO use packaging format (bccomp for float)
            $subject->setVirtualStock($virtual);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateEstimatedDateOfArrival(StockSubjectInterface $subject)
    {
        $currentDate = $subject->getEstimatedDateOfArrival();

        // Abort if subject's in stock equals his virtual stock
        if ($subject->getInStock() == $subject->getVirtualStock()) {
            if (null !== $currentDate) {
                $subject->setEstimatedDateOfArrival(null);

                return true;
            }

            return false;
        }

        $date = null;

        // Resolve the minimum estimated date of arrival
        $stockUnits = $this->stockUnitResolver->findPendingOrReady($subject);
        foreach ($stockUnits as $stockUnit) {
            $unitEDA = $stockUnit->getEstimatedDateOfArrival();

            if (null === $date || $date > $unitEDA) {
                $date = $unitEDA;
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

        // "In stock" resolved state
        if (0 < $subject->getInStock()) {
            if ($currentState != StockSubjectStates::STATE_IN_STOCK) {
                $subject->setStockState(StockSubjectStates::STATE_IN_STOCK);

                return true;
            }

            return false;
        }

        // "Pre order" resolved state
        if (0 < $subject->getVirtualStock() && null !== $subject->getEstimatedDateOfArrival()) {
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

        $changed |= $this->updateVirtualStock($subject);

        $changed |= $this->updateEstimatedDateOfArrival($subject);

        return $this->updateStockState($subject) || $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateFromStockUnitChange(StockSubjectInterface $subject, StockUnitInterface $stockUnit)
    {
        // TODO prevent call while not in a persistence phase (flush).

        $cs = $this->persistenceHelper->getChangeSet($stockUnit);

        // Abort if none of the tracked properties has changed
        if (!(
            isset($cs['reservedQuantity']) ||
            isset($cs['orderedQuantity']) ||
            isset($cs['deliveredQuantity']) ||
            isset($cs['estimatedDateOfArrival'])
        )) {
            return false;
        }

        $changed = false;

        $reservedDelta = $orderedDelta = $deliveredDelta = 0;

        // Resolve delta quantities
        if (isset($cs['reservedQuantity'])) {
            $reservedDelta = ((float)$cs['reservedQuantity'][1]) - ((float)$cs['reservedQuantity'][0]);
        }
        if (isset($cs['orderedQuantity'])) {
            $orderedDelta = ((float)$cs['orderedQuantity'][1]) - ((float)$cs['orderedQuantity'][0]);
        }
        if (isset($cs['deliveredQuantity'])) {
            $deliveredDelta = ((float)$cs['deliveredQuantity'][1]) - ((float)$cs['deliveredQuantity'][0]);
        }

        // In stock change
        if (0 != $inStockDelta = $deliveredDelta - $reservedDelta) {
            $changed |= $this->updateInStock($subject, $inStockDelta);
        }

        // Virtual stock change
        if (0 != $virtualStockDelta = $orderedDelta - $reservedDelta) {
            $changed |= $this->updateVirtualStock($subject, $virtualStockDelta);
        }

        // EDA change
        if ($changed || isset($cs['estimatedDateOfArrival'])) {
            $changed |= $this->updateEstimatedDateOfArrival($subject);
        }

        return $this->updateStockState($subject) || $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateFromStockUnitRemoval(StockSubjectInterface $subject, StockUnitInterface $stockUnit)
    {
        // TODO prevent call while not in a persistence phase (flush).

        $changed = false;

        // This (in stock) should never happen as stock unit removal is prevented when
        // delivered, reserved or shipped quantities are greater than zero.
        if (0 < $inStock = $stockUnit->getInStockQuantity()) {
            $changed |= $this->updateInStock($subject, -$inStock);
        }

        if (0 < $virtualStock = $stockUnit->getVirtualStockQuantity()) {
            $changed |= $this->updateVirtualStock($subject, -$virtualStock);
        }

        // Update the estimated date of arrival
        $changed |= $this->updateEstimatedDateOfArrival($subject);

        return $this->updateStockState($subject) || $changed;
    }
}

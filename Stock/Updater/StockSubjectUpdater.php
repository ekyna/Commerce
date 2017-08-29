<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
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
     * Updates the subjects stock data and state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool
     */
    public function update(StockSubjectInterface $subject)
    {
        $ordered = $received = $sold = $shipped = 0;
        $eda = null; $changed = false;

        // Loop over the 'not closed' stock units.
        // We don't use a dql query to get sums, because database may not reflect
        // real/current data of theses stock units (during a flush event).
        $stockUnits = $this->stockUnitResolver->findNotClosed($subject);
        foreach ($stockUnits as $stockUnit) {
            $sold += $stockUnit->getSoldQuantity();

            if ($stockUnit->getState() !== StockUnitStates::STATE_NEW) {
                $ordered += $stockUnit->getOrderedQuantity();
                $received += $stockUnit->getReceivedQuantity();
                $shipped += $stockUnit->getShippedQuantity();

                if (null !== $date = $stockUnit->getEstimatedDateOfArrival()) {
                    if (null === $eda || $eda > $date) {
                        $eda = $date;
                    }
                }
            }
        }

        // In stock
        $inStock = $received - $shipped;
        if (0 > $inStock) {
            $inStock = 0;
        }
        if ($inStock != $subject->getInStock()) { // TODO use packaging format (bccomp for float)
            $subject->setInStock($inStock);
            $changed = true;
        }

        // Available stock
        $availableStock = $received - $sold;
        if (0 > $availableStock) {
            $availableStock = 0;
        }
        if ($availableStock != $subject->getAvailableStock()) { // TODO use packaging format (bccomp for float)
            $subject->setAvailableStock($availableStock);
            $changed = true;
        }

        // Virtual stock
        $virtualStock = $ordered - $sold;
        if ($virtualStock != $subject->getVirtualStock()) { // TODO use packaging format (bccomp for float)
            $subject->setVirtualStock($virtualStock);
            $changed = true;
        }

        // Estimated date of arrival
        $currentDate = $subject->getEstimatedDateOfArrival();
        // Set null if we do not expect supplier deliveries
        if ($ordered <= $received) {
            if (null !== $currentDate) {
                $subject->setEstimatedDateOfArrival(null);
                $changed = true;
            }
        } else {
            // EDA must be greater than today's first second
            $today = new \DateTime();
            $today->setTime(0, 0, 0);
            if ($eda && $today < $eda) {
                if ($eda != $currentDate) {
                    $subject->setEstimatedDateOfArrival($eda);
                    $changed = true;
                }
            } elseif (null !== $currentDate) {
                $subject->setEstimatedDateOfArrival(null);
                $changed = true;
            }
        }

        if ($changed) {
            $changed |= $this->updateStockState($subject);
        }

        return $changed;
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

        // "Available stock" resolved state
        if (0 < $subject->getAvailableStock()) {
            if ($currentState != StockSubjectStates::STATE_IN_STOCK) {
                $subject->setStockState(StockSubjectStates::STATE_IN_STOCK);

                return true;
            }

            return false;
        }

        // "Pre order" resolved state
        if (0 < $subject->getVirtualStock()) {
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
}

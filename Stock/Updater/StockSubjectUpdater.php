<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockSubjectUpdater
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectUpdater implements StockSubjectUpdaterInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitResolverInterface
     */
    protected $stockUnitResolver;

    /**
     * @var SupplierProductRepositoryInterface
     */
    protected $supplierProductRepository;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface         $persistenceHelper
     * @param StockUnitResolverInterface         $stockUnitResolver
     * @param SupplierProductRepositoryInterface $supplierProductRepository
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitResolverInterface $stockUnitResolver,
        SupplierProductRepositoryInterface $supplierProductRepository
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUnitResolver = $stockUnitResolver;
        $this->supplierProductRepository = $supplierProductRepository;
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
        // If subject stock mode is "inherited", do nothing.
        if ($subject->getStockMode() === StockSubjectModes::MODE_INHERITED) {
            // TODO stock inheritance system
            return false;
        }

        $ordered = $received = $sold = $shipped = 0;
        $eda = null;
        $changed = false;

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

                if ($stockUnit->getState() !== StockUnitStates::STATE_CLOSED) {
                    if (null !== $date = $stockUnit->getEstimatedDateOfArrival()) {
                        if (null === $eda || $eda > $date) {
                            $eda = $date;
                        }
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
        // Set null if we do not expect supplier deliveries
        if ($ordered <= $received) {
            $eda = null;
        }

        // Supplier product min eda for "auto" and "just in time" subjects
        if (
            null === $eda &&
            $subject->getStockMode() !== StockSubjectModes::MODE_MANUAL &&
            $subject instanceof SubjectInterface
        ) {
            $eda = $this->supplierProductRepository->getMinEstimatedDateOfArrivalBySubject($subject);
        }

        $eda = $this->nullDateIfLowerThanToday($eda);

        if ($eda !== $subject->getEstimatedDateOfArrival()) {
            $subject->setEstimatedDateOfArrival(null);
            $changed = true;
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
        // If subject stock mode is "inherited", do nothing.
        if (StockSubjectModes::MODE_INHERITED === $mode = $subject->getStockMode()) {
            // TODO stock inheritance system
            return false;
        }

        $state = StockSubjectStates::STATE_OUT_OF_STOCK;

        // If subject has available stock -> "In stock" state
        if (0 < $subject->getAvailableStock()) {
            $state = StockSubjectStates::STATE_IN_STOCK;
        }

        // Else if subject has virtual stock and estimated date of arrival -> "Pre order" state
        elseif (0 < $subject->getVirtualStock() || null !== $subject->getEstimatedDateOfArrival()) {
            $state = StockSubjectStates::STATE_PRE_ORDER;
        }

        // Else if stock mode is "Auto" or "Just in time"
        elseif ($mode !== StockSubjectModes::MODE_MANUAL) {
            $available = 0;
            if ($subject instanceof SubjectInterface) {
                $available = $this->supplierProductRepository->getAvailableQuantitySumBySubject($subject);
            }

            $eda = $this->nullDateIfLowerThanToday($subject->getEstimatedDateOfArrival());

            // If suppliers has available stock
            if (0 < $available) {
                $state = $mode === StockSubjectModes::MODE_JUST_IN_TIME ?
                    // "In stock" state for "Just in time" mode
                    StockSubjectStates::STATE_IN_STOCK :
                    // "Pre order" state for "Auto" mode
                    StockSubjectStates::STATE_PRE_ORDER;
            } elseif (null !== $eda && $mode === StockSubjectModes::MODE_AUTO) {
                // "Pre order" state for "Auto" mode
                $state = StockSubjectStates::STATE_PRE_ORDER;
            }
        }

        // If "Just in time" mode and "out of stock" state.
        if ($state === StockSubjectStates::STATE_OUT_OF_STOCK && $mode === StockSubjectModes::MODE_JUST_IN_TIME) {
            // Fallback to "Pre order" state
            $state = StockSubjectStates::STATE_PRE_ORDER;
        }

        return $this->setSubjectState($subject, $state);
    }

    /**
     * Returns the given date or null if it's lower than today.
     *
     * @param \DateTime|null $eda
     *
     * @return \DateTime|null
     */
    private function nullDateIfLowerThanToday(\DateTime $eda = null)
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        if ($eda && $today > $eda) {
            $eda = null;
        }

        return $eda;
    }

    /**
     * Sets the subject's stock state.
     *
     * @param StockSubjectInterface $subject
     * @param string                $state
     *
     * @return bool Whether the state has been changed.
     */
    private function setSubjectState(StockSubjectInterface $subject, $state)
    {
        if ($subject->getStockState() != $state) {
            $subject->setStockState($state);

            return true;
        }

        return false;
    }
}

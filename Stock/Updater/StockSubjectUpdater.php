<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;

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
     * @var SupplierProductRepositoryInterface
     */
    protected $supplierProductRepository;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface         $stockUnitResolver
     * @param SupplierProductRepositoryInterface $supplierProductRepository
     */
    public function __construct(
        StockUnitResolverInterface $stockUnitResolver,
        SupplierProductRepositoryInterface $supplierProductRepository
    ) {
        $this->stockUnitResolver = $stockUnitResolver;
        $this->supplierProductRepository = $supplierProductRepository;
    }

    /**
     * @inheritdoc
     */
    public function update(StockSubjectInterface $subject)
    {
        // If subject stock is compound, do nothing.
        if ($subject->isStockCompound()) {
            // TODO stock inheritance system
            return false;
        }

        // If subject stock mode is "disabled", reset to zero and set state to "In stock".
        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            $changed = $this->setSubjectData($subject);
            $changed |= $this->setSubjectState($subject, StockSubjectStates::STATE_IN_STOCK);
            return $changed;
        }

        $ordered = $received = $sold = $shipped = 0;
        $eda = null;

        // Loop over the 'not closed' stock units.
        // We don't use a dql query to get sums, because database may not reflect
        // real/current data of theses stock units (during a flush event).
        $stockUnits = $this->stockUnitResolver->findNotClosed($subject);
        foreach ($stockUnits as $stockUnit) {
            $sold += $stockUnit->getSoldQuantity();

            if ($stockUnit->getState() !== StockUnitStates::STATE_NEW) {
                $ordered += $o = $stockUnit->getOrderedQuantity();
                $received += $r = $stockUnit->getReceivedQuantity();
                $shipped += $s = $stockUnit->getShippedQuantity();

                // Ignore EDA if stock unit his fully received
                if (0 < $o && 0 < $r && $r >= $o) {
                    continue;
                }

                // Skip null EDA
                if (null === $date = $stockUnit->getEstimatedDateOfArrival()) {
                    continue;
                }

                // Keep lowest EDA
                if (null === $eda || $eda > $date) {
                    $eda = $date;
                }
            }
        }

        // In stock
        $inStock = $received - $shipped;
        if (0 > $inStock) $inStock = 0;

        // Available stock
        $availableStock = $received - $sold;
        if (0 > $availableStock) $availableStock = 0;

        // Virtual stock
        $virtualStock = $ordered - $sold;

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

        $changed = $this->setSubjectData($subject, $inStock, $availableStock, $virtualStock, $eda);

        $changed |= $this->updateStockState($subject);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateStockState(StockSubjectInterface $subject)
    {
        // If subject stock is compound, do nothing.
        if ($subject->isStockCompound()) {
            // TODO stock inheritance system
            return false;
        }

        $mode = $subject->getStockMode();

        // If subject stock mode is "disabled" -> "In stock" state.
        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return $this->setSubjectState($subject, StockSubjectStates::STATE_IN_STOCK);
        }

        $state = StockSubjectStates::STATE_OUT_OF_STOCK;
        $min = $subject->getMinimumOrderQuantity();

        // If subject has available stock -> "In stock" state
        if ($min <= $subject->getAvailableStock()) {
            $state = StockSubjectStates::STATE_IN_STOCK;
        }
        // Else if subject has virtual stock and estimated date of arrival -> "Pre order" state
        elseif ($min <= $subject->getVirtualStock() && null !== $subject->getEstimatedDateOfArrival()) {
            $state = StockSubjectStates::STATE_PRE_ORDER;
        }
        // Else if stock mode is "Auto" or "Just in time"
        elseif ($mode !== StockSubjectModes::MODE_MANUAL) {
            $available = 0;
            $ordered = 0;
            if ($subject instanceof SubjectInterface) {
                $available = $this->supplierProductRepository->getAvailableQuantitySumBySubject($subject);
                // TODO $ordered = $this->supplierProductRepository->getOrderedQuantitySumBySubject($subject);
            }

            $eda = $this->nullDateIfLowerThanToday($subject->getEstimatedDateOfArrival());

            // If suppliers has available stock or is about to get it
            if ($min <= $available || (0 < $ordered && null !== $eda)) {
                $state = StockSubjectStates::STATE_PRE_ORDER;
            }
        }

        // If "Just in time" mode
        if ($mode === StockSubjectModes::MODE_JUST_IN_TIME) {
            // If "out of stock" state
            if ($state === StockSubjectStates::STATE_OUT_OF_STOCK) {
                // Fallback to "Pre order" state
                $state = StockSubjectStates::STATE_PRE_ORDER;
            }
            // Else if "pre order" state
            elseif($state === StockSubjectStates::STATE_PRE_ORDER) {
                // Fallback to "In stock" state
                $state = StockSubjectStates::STATE_IN_STOCK;
            }
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
        if (null === $eda) {
            return null;
        }

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        if ($eda < $today) {
            return null;
        }

        return $eda;
    }

    /**
     * Sets the subject stock data.
     *
     * @param StockSubjectInterface $subject
     * @param float                 $inStock
     * @param float                 $availableStock
     * @param float                 $virtualStock
     * @param \DateTime|null        $eda
     *
     * @return bool Whether the data has been changed.
     */
    private function setSubjectData(
        StockSubjectInterface $subject,
        $inStock = .0,
        $availableStock = .0,
        $virtualStock = .0,
        \DateTime $eda = null
    ) {
        $changed = false;

        if ($inStock != $subject->getInStock()) { // TODO use packaging format (bccomp for float)
            $subject->setInStock($inStock);
            $changed = true;
        }

        if ($availableStock != $subject->getAvailableStock()) { // TODO use packaging format (bccomp for float)
            $subject->setAvailableStock($availableStock);
            $changed = true;
        }

        if ($virtualStock != $subject->getVirtualStock()) { // TODO use packaging format (bccomp for float)
            $subject->setVirtualStock($virtualStock);
            $changed = true;
        }

        if ($eda !== $subject->getEstimatedDateOfArrival()) {
            $subject->setEstimatedDateOfArrival($eda);
            $changed = true;
        }

        return $changed;
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

<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Stock\Model\StockComponent;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
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
     * @var array
     */
    protected $defaults;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface         $stockUnitResolver
     * @param SupplierProductRepositoryInterface $supplierProductRepository
     * @param array                              $defaults
     */
    public function __construct(
        StockUnitResolverInterface $stockUnitResolver,
        SupplierProductRepositoryInterface $supplierProductRepository,
        array $defaults = []
    ) {
        $this->stockUnitResolver = $stockUnitResolver;
        $this->supplierProductRepository = $supplierProductRepository;
        $this->defaults = array_replace([
            'stock_mode'             => StockSubjectModes::MODE_AUTO,
            'stock_floor'            => 0,
            'replenishment_time'     => 2,
            'minimum_order_quantity' => 1,
            'quote_only'             => false,
            'end_of_life'            => false,
        ], $defaults);
    }

    /**
     * @inheritdoc
     */
    public function reset(StockSubjectInterface $subject): void
    {
        $map = [
            'stock_mode'             => 'setStockMode',
            'stock_floor'            => 'setStockFloor',
            'replenishment_time'     => 'setReplenishmentTime',
            'minimum_order_quantity' => 'setMinimumOrderQuantity',
            'quote_only'             => 'setQuoteOnly',
            'end_of_life'            => 'setEndOfLife',
        ];

        foreach ($map as $key => $method) {
            if (isset($this->defaults[$key])) {
                $subject->{$method}($this->defaults[$key]);
            }
        }

        $subject
            ->setInStock(0)
            ->setAvailableStock(0)
            ->setVirtualStock(0)
            ->setEstimatedDateOfArrival();
    }

    /**
     * @inheritdoc
     */
    public function update(StockSubjectInterface $subject): bool
    {
        // If subject stock is compound, do nothing.
        if ($subject->isStockCompound()) {
            return $this->updateCompound($subject);
        }

        // If subject stock mode is "disabled", reset to zero and set state to "In stock".
        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            $changed = $this->setSubjectData($subject);
            $changed |= $this->setSubjectState($subject, StockSubjectStates::STATE_IN_STOCK);

            return $changed;
        }

        $ordered = $received = $adjusted = $sold = $shipped = 0;
        $eda = null;

        // Loop over the 'not closed' stock units.
        // We don't use a dql query to get sums, because database may not reflect
        // real/current data of these stock units (during a flush event).
        // The stock unit resolver uses the stock unit cache.
        $stockUnits = $this->stockUnitResolver->findNotClosed($subject);
        foreach ($stockUnits as $stockUnit) {
            $sold += $s = $stockUnit->getSoldQuantity();
            $shipped += $stockUnit->getShippedQuantity();
            $adjusted += $a = $stockUnit->getAdjustedQuantity();

            if ($stockUnit->getState() === StockUnitStates::STATE_NEW) {
                continue;
            }

            $ordered += $o = $stockUnit->getOrderedQuantity();
            $received += $r = $stockUnit->getReceivedQuantity();

            // Ignore EDA if stock unit his fully sold
            if ($s >= $o + $a) {
                continue;
            }
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

        // In stock
        $inStock = $received + $adjusted - $shipped;
        if (0 > $inStock) {
            $inStock = 0;
        }

        // Available stock
        $availableStock = $received + $adjusted - $sold;
        if (0 > $availableStock) {
            $availableStock = 0;
        }

        // Virtual stock
        $virtualStock = $ordered + $adjusted - $sold;

        // Estimated date of arrival
        // Set null if we do not expect supplier deliveries
        if ($ordered <= $received) {
            $eda = null;
        }

        // Supplier product min eda for "auto" and "just in time" subjects
        if (null === $eda && $subject->getStockMode() !== StockSubjectModes::MODE_MANUAL) {
            $eda = $this->supplierProductRepository->getMinEstimatedDateOfArrivalBySubject($subject);
        }

        $eda = $this->nullDateIfLowerThanToday($eda);

        $changed = $this->setSubjectData($subject, $inStock, $availableStock, $virtualStock, $eda);

        // TODO Resolve components

        $changed |= $this->updateStockState($subject);

        return $changed;
    }

    /**
     * Updates the subject is stock data from its composition.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool
     */
    protected function updateCompound(StockSubjectInterface $subject): bool
    {
        $unit = $subject->getUnit();
        $justInTime = $disabled = $resupply = true;
        $inStock = $virtualStock = $availableStock = $eda = null;

        foreach ($subject->getStockComposition() as $component) {
            // Array represents user choices -> Select best component.
            if (is_array($component)) {
                if (null === $component = $this->pickBestComponent($component)) {
                    continue;
                }
            }

            $child = $component->getSubject();
            $quantity = $component->getQuantity();

            // Mode
            if ($child->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                continue;
            }

            // State
            $disabled = false;
            if ($child->getStockMode() !== StockSubjectModes::MODE_JUST_IN_TIME) {
                $justInTime = false;
            }

            // In stock
            $childInStock = Units::round($child->getInStock() / $quantity, $unit);
            if (null === $inStock || $childInStock < $inStock) {
                $inStock = $childInStock;
            }

            // Available stock
            $childAvailableStock = Units::round($child->getAvailableStock() / $quantity, $unit);
            if (null === $availableStock || $childAvailableStock < $availableStock) {
                $availableStock = $childAvailableStock;
            }

            // Virtual stock
            $childVirtualStock = Units::round($child->getVirtualStock() / $quantity, $unit);
            if (null === $virtualStock || $childVirtualStock <= $virtualStock) {
                $virtualStock = $childVirtualStock;
            }

            // Estimated date of arrival
            if (null !== $childEda = $child->getEstimatedDateOfArrival()) {
                if ($resupply && (null === $eda || $childEda > $eda)) {
                    $eda = $childEda;
                }
            } elseif (0 >= $childAvailableStock) {
                $resupply = false;
                $eda = null;
            }
        }

        if (null === $inStock) {
            $inStock = 0;
        }
        if (null === $availableStock) {
            $availableStock = 0;
        }
        if (null === $virtualStock) {
            $virtualStock = 0;
        }

        if ($disabled) {
            $mode = StockSubjectModes::MODE_DISABLED;
            $state = StockSubjectStates::STATE_IN_STOCK;
        } else {
            $mode = $justInTime ? StockSubjectModes::MODE_JUST_IN_TIME : StockSubjectModes::MODE_AUTO;

            $state = StockSubjectStates::STATE_OUT_OF_STOCK;
            if (0 < $availableStock) {
                $state = StockSubjectStates::STATE_IN_STOCK;
            } elseif (0 < $virtualStock && $eda) {
                $state = StockSubjectStates::STATE_PRE_ORDER;
            }

            // If "Just in time" mode
            if ($mode === StockSubjectModes::MODE_JUST_IN_TIME) {
                // If "out of stock" state
                if ($state === StockSubjectStates::STATE_OUT_OF_STOCK) {
                    // Fallback to "Pre order" state
                    $state = StockSubjectStates::STATE_PRE_ORDER;
                } // Else if "pre order" state
                elseif ($state === StockSubjectStates::STATE_PRE_ORDER) {
                    // Fallback to "In stock" state
                    $state = StockSubjectStates::STATE_IN_STOCK;
                }
            }
        }

        $changed = $this->setSubjectData($subject, $inStock, $availableStock, $virtualStock, $eda);
        $changed |= $this->setSubjectMode($subject, $mode);
        $changed |= $this->setSubjectState($subject, $state);

        return $changed;
    }

    /**
     * Updates the subject's stock state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool
     */
    protected function updateStockState(StockSubjectInterface $subject): bool
    {
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
        } // Else if subject has virtual stock and estimated date of arrival -> "Pre order" state
        elseif ($min <= $subject->getVirtualStock() && null !== $subject->getEstimatedDateOfArrival()) {
            $state = StockSubjectStates::STATE_PRE_ORDER;
        } // Else if stock mode is "Auto" or "Just in time"
        elseif (!$subject->isStockCompound() && $mode !== StockSubjectModes::MODE_MANUAL) {
            // Supplier product availability
            $available = $this->supplierProductRepository->getAvailableQuantitySumBySubject($subject);
            $ordered = $this->supplierProductRepository->getOrderedQuantitySumBySubject($subject);
            $eda = $this->nullDateIfLowerThanToday($subject->getEstimatedDateOfArrival());

            // If suppliers has available stock or is about to get it
            if ($min <= $available || ($min <= $ordered && null !== $eda)) {
                $state = StockSubjectStates::STATE_PRE_ORDER;
            }
        }

        // If "Just in time" mode
        if ($mode === StockSubjectModes::MODE_JUST_IN_TIME) {
            // If "out of stock" state
            if ($state === StockSubjectStates::STATE_OUT_OF_STOCK) {
                // Fallback to "Pre order" state
                $state = StockSubjectStates::STATE_PRE_ORDER;
            } // Else if "pre order" state
            elseif ($state === StockSubjectStates::STATE_PRE_ORDER) {
                // Fallback to "In stock" state
                $state = StockSubjectStates::STATE_IN_STOCK;
            }
        }

        return $this->setSubjectState($subject, $state);
    }

    /**
     * Returns the best component.
     *
     * @param array $components
     *
     * @return StockComponent|null
     */
    private function pickBestComponent(array $components): ?StockComponent
    {
        $best = null;

        foreach ($components as $component) {
            if (!$best || $this->isBetterComponent($component, $best)) {
                $best = $component;
            }
        }

        return $best;
    }

    /**
     * Returns true if the A choice is better than the B choice.
     *
     * @param StockComponent $a
     * @param StockComponent $b
     *
     * @return bool
     */
    private function isBetterComponent(StockComponent $a, StockComponent $b): bool
    {
        // TODO Packaging format

        $subjectA = $a->getSubject();
        $subjectB = $b->getSubject();

        if (StockSubjectModes::isBetterMode($subjectA->getStockMode(), $subjectB->getStockMode())) {
            return true;
        }

        if (StockSubjectStates::isBetterState($subjectA->getStockState(), $subjectB->getStockState())) {
            return true;
        }

        // Available stock
        if (0 < $aAvailable = $subjectA->getAvailableStock() / $a->getQuantity()) {
            $bAvailable = $subjectB->getAvailableStock() / $b->getQuantity();
            if ($bAvailable < $aAvailable) {
                return true;
            }
        }

        // Virtual stock
        if (0 < $aVirtual = $subjectA->getVirtualStock() / $a->getQuantity()) {
            $aEda = $subjectA->getEstimatedDateOfArrival();
            $bEda = $subjectB->getEstimatedDateOfArrival();

            if (is_null($bEda) && is_null($aEda)) {
                $bVirtual = $subjectB->getVirtualStock() / $b->getQuantity();
                if ($bVirtual < $aVirtual) {
                    return true;
                }
            }

            if ($aEda && (is_null($bEda) || ($bEda > $aEda))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the given date or null if it's lower than today.
     *
     * @param \DateTime|null $eda
     *
     * @return \DateTime|null
     */
    private function nullDateIfLowerThanToday(\DateTime $eda = null): ?\DateTime
    {
        if (null === $eda) {
            return null;
        }

        $today = new \DateTime();
        $today->setTime(0, 0);

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
    ): bool {
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
     * Sets the subject's stock mode.
     *
     * @param StockSubjectInterface $subject
     * @param string                $mode
     *
     * @return bool Whether the mode has been changed.
     */
    private function setSubjectMode(StockSubjectInterface $subject, string $mode): bool
    {
        if ($subject->getStockMode() != $mode) {
            $subject->setStockMode($mode);

            return true;
        }

        return false;
    }

    /**
     * Sets the subject's stock state.
     *
     * @param StockSubjectInterface $subject
     * @param string                $state
     *
     * @return bool Whether the state has been changed.
     */
    private function setSubjectState(StockSubjectInterface $subject, string $state): bool
    {
        if ($subject->getStockState() != $state) {
            $subject->setStockState($state);

            return true;
        }

        return false;
    }
}

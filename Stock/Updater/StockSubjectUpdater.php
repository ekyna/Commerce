<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Updater;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
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
    protected StockUnitResolverInterface         $stockUnitResolver;
    protected SupplierProductRepositoryInterface $supplierProductRepository;
    protected array                              $defaults;

    private ?StockCompositionSorter $sorter = null; // TODO Service ?

    public function __construct(
        StockUnitResolverInterface         $stockUnitResolver,
        SupplierProductRepositoryInterface $supplierProductRepository,
        array                              $defaults = []
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

    public function reset(StockSubjectInterface $subject): void
    {
        $map = [
            'stock_mode'             => ['setStockMode', false],
            'stock_floor'            => ['setStockFloor', true],
            'replenishment_time'     => ['setReplenishmentTime', false],
            'minimum_order_quantity' => ['setMinimumOrderQuantity', true],
            'quote_only'             => ['setQuoteOnly', false],
            'end_of_life'            => ['setEndOfLife', false],
        ];

        foreach ($map as $key => [$method, $decimal]) {
            if (!isset($this->defaults[$key])) {
                continue;
            }

            $value = $decimal ? new Decimal($this->defaults[$key]) : $this->defaults[$key];

            $subject->{$method}($value);
        }

        $subject
            ->setInStock(new Decimal(0))
            ->setAvailableStock(new Decimal(0))
            ->setVirtualStock(new Decimal(0))
            ->setEstimatedDateOfArrival(null);
    }

    /**
     * @inheritDoc
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

            return $this->setSubjectState($subject, StockSubjectStates::STATE_IN_STOCK) || $changed;
        }

        $ordered = new Decimal(0);
        $received = new Decimal(0);
        $adjusted = new Decimal(0);
        $sold = new Decimal(0);
        $shipped = new Decimal(0);
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

            // Keep the lowest EDA
            if (null === $eda || $eda > $date) {
                $eda = $date;
            }
        }

        // In stock
        $inStock = $received + $adjusted - $shipped;
        if (0 > $inStock) {
            $inStock = new Decimal(0);
        }

        // Available stock
        $availableStock = $received + $adjusted - $sold;
        if (0 > $availableStock) {
            $availableStock = new Decimal(0);
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

        return $this->updateStockState($subject) || $changed;
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
        $justInTime = $disabled = $resupply = true;
        $inStock = $virtualStock = $availableStock = $eda = null;
        $replenishmentTime = 0;

        $composition = $this->getSorter()->sort($subject);

        foreach ($composition as $component) {
            $child = $component->getSubject();

            // Mode
            if ($child->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                continue;
            }
            $disabled = false;
            if ($child->getStockMode() !== StockSubjectModes::MODE_JUST_IN_TIME) {
                $justInTime = false;
            }

            $quantity = $component->getQuantity();

            // In stock
            $childInStock = $child->getInStock()->div($quantity);
            if (null === $inStock || $childInStock < $inStock) {
                $inStock = $childInStock;
            }

            // Available stock
            $childAvailableStock = $child->getAvailableStock()->div($quantity);
            if (null === $availableStock || $childAvailableStock < $availableStock) {
                $availableStock = $childAvailableStock;
            }

            // Virtual stock
            $childVirtualStock = $child->getVirtualStock()->div($quantity);
            if (null === $virtualStock || $childVirtualStock <= $virtualStock) {
                $virtualStock = $childVirtualStock;
            }

            // Estimated date of arrival
            if ($resupply) {
                if (null !== $childEda = $child->getEstimatedDateOfArrival()) {
                    if (
                        null === $eda
                        || (0 < $availableStock && $childEda < $eda)  // If available, gather the soonest eda
                        || (0 >= $availableStock && $childEda > $eda) // If NOT available, gather the latest eda
                    ) {
                        $eda = $childEda;
                    }
                } elseif (0 >= $availableStock) {
                    $resupply = false;
                    $eda = null;
                }
            }

            // ReplenishmentTime
            if ($child->getReplenishmentTime() > $replenishmentTime) {
                $replenishmentTime = $child->getReplenishmentTime();
            }
        }

        if (null === $inStock) {
            $inStock = new Decimal(0);
        }
        if (null === $availableStock) {
            $availableStock = new Decimal(0);
        }
        if (null === $virtualStock) {
            $virtualStock = new Decimal(0);
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
                    // Fallback to "pre-order" state
                    $state = StockSubjectStates::STATE_PRE_ORDER;
                } // Else if "pre-order" state
                elseif ($state === StockSubjectStates::STATE_PRE_ORDER) {
                    // Fallback to "in stock" state
                    $state = StockSubjectStates::STATE_IN_STOCK;
                }
            }
        }

        if ($replenishmentTime !== $subject->getReplenishmentTime()) {
            $subject->setReplenishmentTime($replenishmentTime);
        }

        $changed = $this->setSubjectData($subject, $inStock, $availableStock, $virtualStock, $eda);

        $changed = $this->setSubjectMode($subject, $mode) || $changed;

        return $this->setSubjectState($subject, $state) || $changed;
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
            } // Else if "pre-order" state
            elseif ($state === StockSubjectStates::STATE_PRE_ORDER) {
                // Fallback to "In stock" state
                $state = StockSubjectStates::STATE_IN_STOCK;
            }
        }

        return $this->setSubjectState($subject, $state);
    }

    /**
     * Returns the given date or null if it's lower than today.
     */
    private function nullDateIfLowerThanToday(?DateTimeInterface $eda): ?DateTimeInterface
    {
        if (null === $eda) {
            return null;
        }

        $today = new DateTime();
        $today->setTime(0, 0);

        if ($eda < $today) {
            return null;
        }

        return $eda;
    }

    /**
     * Sets the subject stock data.
     *
     * @return bool Whether the data has been changed.
     */
    private function setSubjectData(
        StockSubjectInterface $subject,
        Decimal               $inStock = null,
        Decimal               $availableStock = null,
        Decimal               $virtualStock = null,
        DateTimeInterface     $eda = null
    ): bool {
        $changed = false;

        $inStock = $inStock ?: new Decimal(0);
        $availableStock = $availableStock ?: new Decimal(0);
        $virtualStock = $virtualStock ?: new Decimal(0);

        if (!$subject->getInStock()->equals($inStock)) {
            $subject->setInStock($inStock);
            $changed = true;
        }

        if (!$subject->getAvailableStock()->equals($availableStock)) {
            $subject->setAvailableStock($availableStock);
            $changed = true;
        }

        if (!$subject->getVirtualStock()->equals($virtualStock)) {
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
     * @return bool Whether the mode has been changed.
     */
    private function setSubjectMode(StockSubjectInterface $subject, string $mode): bool
    {
        if ($mode !== $subject->getStockMode()) {
            $subject->setStockMode($mode);

            return true;
        }

        return false;
    }

    /**
     * Sets the subject's stock state.
     *
     * @return bool Whether the state has been changed.
     */
    private function setSubjectState(StockSubjectInterface $subject, string $state): bool
    {
        if ($state !== $subject->getStockState()) {
            $subject->setStockState($state);

            return true;
        }

        return false;
    }

    private function getSorter(): StockCompositionSorter
    {
        if (null !== $this->sorter) {
            return $this->sorter;
        }

        return new StockCompositionSorter();
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model\StockComponent;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;

use function array_filter;
use function array_map;
use function is_array;
use function is_null;
use function usort;

/**
 * Class StockCompositionSorter
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockCompositionSorter
{
    /**
     * Returns the sorted subject stock composition.
     *
     * @return array<int, StockComponent>
     */
    public function sort(StockSubjectInterface $subject): array
    {
        if (!$subject->isStockCompound()) {
            throw new LogicException('Subject is not compound.');
        }

        $composition = $subject->getStockComposition();

        // Reduce choices to the bests components
        /** @var StockComponent|array $composition */
        $composition = array_map(function($entry) {
            if (is_array($entry)) {
                return $this->pickBestComponent($entry);
            }

            return $entry;
        }, $composition);

        // Remove null components
        $composition = array_filter($composition);

        // Sort components
        usort($composition, function(StockComponent $a, StockComponent $b) {
            return $this->isBetterComponent($a, $b) ? 1 : -1;
        });

        return $composition;
    }

    /**
     * Returns the best component.
     *
     * @param array<int, StockComponent> $components
     */
    private function pickBestComponent(array $components): ?StockComponent
    {
        $best = null;

        foreach ($components as $component) {
            if (null === $best || $this->isBetterComponent($component, $best)) {
                $best = $component;
            }
        }

        return $best;
    }

    /**
     * Returns true if the A choice is better than the B choice.
     */
    private function isBetterComponent(StockComponent $a, StockComponent $b): bool
    {
        if (StockSubjectModes::isBetterMode($a->getStockMode(), $b->getStockMode())) {
            return true;
        }

        if (StockSubjectStates::isBetterState($a->getStockState(), $b->getStockState())) {
            return true;
        }

        // Available stock
        if (0 < $aAvailable = $a->getAvailableStock()) {
            $bAvailable = $b->getAvailableStock();
            if ($bAvailable < $aAvailable) {
                return true;
            }
        }

        // Virtual stock
        if (0 < $aVirtual = $a->getVirtualStock()) {
            $aEda = $a->getEstimatedDateOfArrival();
            $bEda = $b->getEstimatedDateOfArrival();

            if (is_null($bEda) && is_null($aEda)) {
                $bVirtual = $b->getVirtualStock();
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
}

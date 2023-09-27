<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentDataInterface;

/**
 * Interface AdjustmentBuilderInterface
 * @package Ekyna\Component\Commerce\Common\Builder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AdjustmentBuilderInterface
{
    /**
     * Builds the adjustments regarding the given data and type.
     *
     * @param string                                 $type
     * @param AdjustableInterface                    $adjustable
     * @param iterable<int, AdjustmentDataInterface> $data
     * @param bool                                   $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    public function buildAdjustments(
        string              $type,
        AdjustableInterface $adjustable,
        iterable            $data,
        bool                $persistence = false
    ): bool;

    /**
     * Turns all immutable adjustments into mutable adjustments.
     *
     * @param AdjustableInterface $adjustable
     * @param array               $types
     * @param bool                $persistence
     * @return void
     */
    public function makeAdjustmentsMutable(
        AdjustableInterface $adjustable,
        array               $types = [],
        bool                $persistence = false
    ): void;

    /**
     * Clears all mutable adjustments.
     *
     * @param AdjustableInterface $adjustable
     * @param array<string>       $types
     * @param bool                $persistence
     * @return void
     */
    public function clearMutableAdjustments(
        AdjustableInterface $adjustable,
        array               $types = [],
        bool                $persistence = false
    ): void;
}

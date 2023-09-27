<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface AdjustableInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AdjustableInterface
{
    /**
     * Returns whether the adjustable has adjustments or not, optionally filtered by type.
     */
    public function hasAdjustments(string $type = null): bool;

    /**
     * Returns whether the adjustable has the adjustment or not.
     */
    public function hasAdjustment(AdjustmentInterface $adjustment): bool;

    /**
     * Adds the adjustment.
     *
     * @return $this|AdjustableInterface
     */
    public function addAdjustment(AdjustmentInterface $adjustment): AdjustableInterface;

    /**
     * Removes the adjustment.
     *
     * @return $this|AdjustableInterface
     */
    public function removeAdjustment(AdjustmentInterface $adjustment): AdjustableInterface;

    /**
     * Returns the adjustments, optionally filtered by type.
     *
     * @return Collection<AdjustmentInterface>
     */
    public function getAdjustments(string $type = null): Collection;
}

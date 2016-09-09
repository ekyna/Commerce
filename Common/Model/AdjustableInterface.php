<?php

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
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasAdjustments($type = null);

    /**
     * Returns whether the adjustable has the adjustment or not.
     *
     * @param AdjustmentInterface $adjustment
     *
     * @return bool
     */
    public function hasAdjustment(AdjustmentInterface $adjustment);

    /**
     * Adds the adjustment.
     *
     * @param AdjustmentInterface $adjustment
     *
     * @return $this|AdjustableInterface
     */
    public function addAdjustment(AdjustmentInterface $adjustment);

    /**
     * Removes the adjustment.
     *
     * @param AdjustmentInterface $adjustment
     *
     * @return $this|AdjustableInterface
     */
    public function removeAdjustment(AdjustmentInterface $adjustment);

    /**
     * Returns the adjustments, optionally filtered by type.
     *
     * @param string $type
     *
     * @return Collection|AdjustmentInterface[]
     */
    public function getAdjustments($type = null);
}

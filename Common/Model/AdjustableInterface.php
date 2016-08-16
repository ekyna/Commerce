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
     * Returns the adjustments, optionally filtered by type.
     *
     * @param string $type
     *
     * @return Collection|AdjustmentInterface[]
     */
    public function getAdjustments($type = null);
}

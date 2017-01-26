<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait AdjustableTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait AdjustableTrait
{
    /**
     * @var ArrayCollection|AdjustmentInterface[]
     */
    protected $adjustments;


    /**
     * Initializes the adjustments.
     */
    protected function initializeAdjustments()
    {
        $this->adjustments = new ArrayCollection();
    }

    /**
     * Returns whether the adjustable has adjustments or not, optionally filtered by type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasAdjustments($type = null)
    {
        if (null !== $type) {
            AdjustmentTypes::isValidType($type);

            return $this->getAdjustments($type)->count();
        }

        return 0 < $this->adjustments->count();
    }

    /**
     * Returns the adjustments, optionally filtered by type.
     *
     * @param string $type
     *
     * @return ArrayCollection|AdjustmentInterface[]
     */
    public function getAdjustments($type = null)
    {
        if (null !== $type) {
            AdjustmentTypes::isValidType($type);

            return $this
                ->adjustments
                ->filter(function (AdjustmentInterface $a) use ($type) {
                    return $a->getType() === $type;
                });
        }

        return $this->adjustments;
    }
}

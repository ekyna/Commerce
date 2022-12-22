<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Trait AdjustableTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait AdjustableTrait
{
    /** @var Collection<int, AdjustmentInterface> */
    protected Collection $adjustments;


    /**
     * Initializes the adjustments.
     */
    protected function initializeAdjustments(): void
    {
        $this->adjustments = new ArrayCollection();
    }

    /**
     * Returns whether the adjustable has adjustments or not, optionally filtered by type.
     */
    public function hasAdjustments(string $type = null): bool
    {
        if (null === $type) {
            return 0 < $this->adjustments->count();
        }

        AdjustmentTypes::isValidType($type);

        return 0 < $this->getAdjustments($type)->count();
    }

    /**
     * Returns the adjustments, optionally filtered by type.
     *
     * @return Collection<int, AdjustmentInterface>
     */
    public function getAdjustments(string $type = null): Collection
    {
        if (null === $type) {
            return $this->adjustments;
        }

        AdjustmentTypes::isValidType($type);

        return $this
            ->adjustments
            ->filter(function (AdjustmentInterface $a) use ($type) {
                return $a->getType() === $type;
            });
    }
}

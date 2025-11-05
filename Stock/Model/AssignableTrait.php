<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

use function is_a;

/**
 * Trait AssignableTrait
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait AssignableTrait
{
    /** @var Collection<AssignmentInterface> */
    protected Collection $stockAssignments;


    protected function initializeAssignments(): void
    {
        $this->stockAssignments = new ArrayCollection();
    }

    public function hasStockAssignment(AssignmentInterface $assignment): bool
    {
        $this->assertAssignmentClass($assignment);

        return $this->stockAssignments->contains($assignment);
    }

    public function addStockAssignment(AssignmentInterface $assignment): AssignableInterface
    {
        $this->assertAssignmentClass($assignment);

        if ($this->hasStockAssignment($assignment)) {
            return $this;
        }

        $this->stockAssignments->add($assignment);
        $assignment->setAssignable($this);

        return $this;
    }

    public function removeStockAssignment(AssignmentInterface $assignment): AssignableInterface
    {
        $this->assertAssignmentClass($assignment);

        if (!$this->hasStockAssignment($assignment)) {
            return $this;
        }

        $this->stockAssignments->removeElement($assignment);
        $assignment->setAssignable(null);

        return $this;
    }

    public function hasStockAssignments(): bool
    {
        return 0 < $this->stockAssignments->count();
    }

    public function getStockAssignments(): Collection
    {
        return $this->stockAssignments;
    }

    protected function assertAssignmentClass(AssignmentInterface $assignment): void
    {
        if (is_a($assignment, $this->getAssignmentClass())) {
            return;
        }

        throw new UnexpectedTypeException($assignment, $this->getAssignmentClass());
    }
}

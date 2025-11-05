<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;

/**
 * Interface AssignableInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AssignableInterface extends SubjectReferenceInterface
{
    public function hasStockAssignment(AssignmentInterface $assignment): bool;

    public function addStockAssignment(AssignmentInterface $assignment): AssignableInterface;

    public function removeStockAssignment(AssignmentInterface $assignment): AssignableInterface;

    public function hasStockAssignments(): bool;

    /**
     * @return Collection<AssignmentInterface>
     */
    public function getStockAssignments(): Collection;

    public function getAssignmentClass(): string;
}

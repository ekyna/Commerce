<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface StockAssignmentsInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAssignmentsInterface
{
    public function hasStockAssignment(StockAssignmentInterface $assignment): bool;

    public function addStockAssignment(StockAssignmentInterface $assignment): StockAssignmentsInterface;

    public function removeStockAssignment(StockAssignmentInterface $assignment): StockAssignmentsInterface;

    public function hasStockAssignments(): bool;

    /**
     * @return Collection|array<StockAssignmentInterface>
     */
    public function getStockAssignments(): Collection;
}

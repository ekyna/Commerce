<?php

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Interface StockAssignmentsInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAssignmentsInterface
{
    /**
     * Adds the stock assignment.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return $this|StockAssignmentsInterface
     */
    public function addStockAssignment(StockAssignmentInterface $assignment);

    /**
     * Removes the stock assignment.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return $this|StockAssignmentsInterface
     */
    public function removeStockAssignment(StockAssignmentInterface $assignment);

    /**
     * Returns whether there is stock assignment.
     *
     * @return bool
     */
    public function hasStockAssignments();

    /**
     * Returns the the stock assignment.
     *
     * @return \Doctrine\Common\Collections\Collection|StockAssignmentInterface[]
     */
    public function getStockAssignments();
}

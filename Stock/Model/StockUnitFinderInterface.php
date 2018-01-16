<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Stock\Model as Stock;

/**
 * Interface StockUnitFinderInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitFinderInterface
{
    /**
     * Finds the subject's new stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findNewBySubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's pending stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findPendingBySubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's ready stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findReadyBySubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's pending or ready stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findPendingOrReadyBySubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's not closed stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findNotClosedBySubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's not fully assigned (to sale items) stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findAssignableBySubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's not linked (to supplier order item) stock unit.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findLinkableBySubject(Stock\StockSubjectInterface $subject);
}

<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;

/**
 * Interface StockUnitResolverInterface
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitResolverInterface
{
    /**
     * Returns the stock unit cache.
     *
     * @return \Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface
     */
    public function getStockUnitCache();

    /**
     * Creates a stock unit for the given subject.
     *
     * @param StockSubjectInterface $subject
     * @param StockUnitInterface    $exceptStockUnit
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface
     */
    public function createBySubject(StockSubjectInterface $subject, StockUnitInterface $exceptStockUnit = null);

    /**
     * Creates a stock unit for the given subject relative.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface
     */
    public function createBySubjectRelative(SubjectRelativeInterface $relative);

    /**
     * Finds the pending stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findPending($subjectOrRelative);

    /**
     * Finds the ready stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findReady($subjectOrRelative);

    /**
     * Finds the pending or ready stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findPendingOrReady($subjectOrRelative);

    /**
     * Finds the not closed stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findNotClosed($subjectOrRelative);

    /**
     * Finds the not fully assigned (to sale items) stock units by subject or relative.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findAssignable($subjectOrRelative);

    /**
     * Finds the not linked (to supplier order item) stock unit by subject or relative.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface|null
     */
    public function findLinkable($subjectOrRelative);
}

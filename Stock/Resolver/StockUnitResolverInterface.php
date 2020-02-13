<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
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
     * Creates a stock unit for the given subject.
     *
     * @param StockSubjectInterface $subject
     * @param StockUnitInterface    $exceptStockUnit
     *
     * @return StockUnitInterface
     */
    public function createBySubject(
        StockSubjectInterface $subject,
        StockUnitInterface $exceptStockUnit = null
    ): StockUnitInterface;

    /**
     * Creates a stock unit for the given subject relative.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return StockUnitInterface
     */
    public function createBySubjectRelative(SubjectRelativeInterface $relative): StockUnitInterface;

    /**
     * Finds the pending stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|StockUnitInterface[]
     */
    public function findPending($subjectOrRelative): array;

    /**
     * Finds the ready stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|StockUnitInterface[]
     */
    public function findReady($subjectOrRelative): array;

    /**
     * Finds the pending or ready stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|StockUnitInterface[]
     */
    public function findPendingOrReady($subjectOrRelative): array;

    /**
     * Finds the not closed stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|StockUnitInterface[]
     */
    public function findNotClosed($subjectOrRelative): array;

    /**
     * Finds the not fully assigned (to sale items) stock units by subject or relative.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|StockUnitInterface[]
     */
    public function findAssignable($subjectOrRelative): array;

    /**
     * Finds the not linked (to supplier order item) stock unit by subject or relative.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return StockUnitInterface|null
     */
    public function findLinkable($subjectOrRelative): ?StockUnitInterface;
}

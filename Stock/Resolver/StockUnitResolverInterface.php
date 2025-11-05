<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;

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
     * @param SubjectReferenceInterface $relative
     *
     * @return StockUnitInterface
     */
    public function createBySubjectReference(SubjectReferenceInterface $relative): StockUnitInterface;

    /**
     * Finds the pending stock units.
     *
     * @param StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative
     *
     * @return array<int, StockUnitInterface>
     */
    public function findPending(StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative): array;

    /**
     * Finds the ready stock units.
     *
     * @param StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative
     *
     * @return array<int, StockUnitInterface>
     */
    public function findReady(StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative): array;

    /**
     * Finds the pending or ready stock units.
     *
     * @param StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative
     *
     * @return array<int, StockUnitInterface>
     */
    public function findPendingOrReady(StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative): array;

    /**
     * Finds the not closed stock units.
     *
     * @param StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative
     *
     * @return array<int, StockUnitInterface>
     */
    public function findNotClosed(StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative): array;

    /**
     * Finds the not fully assigned (to sale items) stock units by subject or relative.
     *
     * @param StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative
     *
     * @return array<int, StockUnitInterface>
     */
    public function findAssignable(StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative): array;

    /**
     * Finds the not linked (to supplier order item) stock unit by subject or relative.
     *
     * @param StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative
     *
     * @return StockUnitInterface|null
     */
    public function findLinkable(StockSubjectInterface|SubjectReferenceInterface $subjectOrRelative): ?StockUnitInterface;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Repository;

use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitFinderInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface StockUnitRepositoryInterface
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<StockUnitInterface>
 */
interface StockUnitRepositoryInterface extends StockUnitFinderInterface, ResourceRepositoryInterface
{
    /**
     * Returns the stock units having real stock.
     *
     * @return array<StockUnitInterface>
     */
    public function findInStock(): array;

    /**
     * Returns the latest not closed stock units.
     *
     * @return array<StockUnitInterface>
     */
    public function findLatestNotClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array;

    /**
     * Returns the latest closed stock units.
     *
     * @return array<StockUnitInterface>
     */
    public function findLatestClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array;

    /**
     * @return array<int, StockAdjustmentInterface>
     */
    public function findAdjustmentsBySubjectAndDateRange(SubjectInterface $subject, ?DateRange $range): array;
}

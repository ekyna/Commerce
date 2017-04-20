<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Repository;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitFinderInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface StockUnitRepositoryInterface
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitRepositoryInterface extends StockUnitFinderInterface, ResourceRepositoryInterface
{
    /**
     * Returns the stock units having real stock.
     *
     * @return StockUnitInterface[]
     */
    public function findInStock(): array;

    /**
     * Returns the latest not closed stock units.
     *
     * @param StockSubjectInterface $subject
     * @param int                   $limit
     *
     * @return StockUnitInterface[]
     */
    public function findLatestNotClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array;

    /**
     * Returns the latest closed stock units.
     *
     * @param StockSubjectInterface $subject
     * @param int                   $limit
     *
     * @return StockUnitInterface[]
     */
    public function findLatestClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array;
}

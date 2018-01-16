<?php

namespace Ekyna\Component\Commerce\Stock\Repository;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitFinderInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

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
    public function findInStock();

    /**
     * Returns the latest closed stock units.
     *
     * @param StockSubjectInterface $subject
     * @param int                   $limit
     *
     * @return StockUnitInterface[]
     */
    public function findLatestClosedBySubject(StockSubjectInterface $subject, $limit = 3);
}

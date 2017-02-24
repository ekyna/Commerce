<?php

namespace Ekyna\Component\Commerce\Stock\Repository;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface StockUnitRepositoryInterface
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Creates a new stock unit.
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface
     */
    public function createNew();

    /**
     * Finds the subject's available or pending stock units.
     *
     * @param StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findAvailableOrPendingBySubject(StockSubjectInterface $subject);

    /**
     * Finds the subject's new stock units.
     *
     * @param StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findNewBySubject(StockSubjectInterface $subject);

    /**
     * Finds the subject's not closed stock units.
     *
     * @param StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findNotClosedSubject(StockSubjectInterface $subject);
}

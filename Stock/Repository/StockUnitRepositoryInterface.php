<?php

namespace Ekyna\Component\Commerce\Stock\Repository;

use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface StockUnitRepositoryInterface
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Stock\StockUnitInterface createNew()
 */
interface StockUnitRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the subject's available or pending stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findAvailableOrPendingBySubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's new stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findNewBySubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's not closed stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findNotClosedSubject(Stock\StockSubjectInterface $subject);

    /**
     * Finds the subject's not fully assigned stock units.
     *
     * @param Stock\StockSubjectInterface $subject
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findAssignableBySubject(Stock\StockSubjectInterface $subject);
}

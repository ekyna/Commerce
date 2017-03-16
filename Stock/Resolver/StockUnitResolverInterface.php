<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Interface StockUnitResolverInterface
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitResolverInterface
{
    /**
     * Creates a stock unit for the given subject relative.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface
     */
    public function createBySubjectRelative(SubjectRelativeInterface $relative);

    /**
     * Creates (and initializes) a stock unit for the given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface
     */
    public function createBySupplierOrderItem(SupplierOrderItemInterface $item);

    /**
     * Finds the available or pending stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findAvailableOrPending($subjectOrRelative);

    /**
     * Finds the not fully assigned stock units by subject or relative.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findAssignable($subjectOrRelative);

    /**
     * Returns the stock unit repository by subject.
     *
     * @param StockSubjectInterface $subject
     *
     * @return StockUnitRepositoryInterface
     *
     * @deprecated Use findAvailableOrPendingStockUnits(StockSubjectInterface $subject)
     */
    public function getRepositoryBySubject(StockSubjectInterface $subject);
}

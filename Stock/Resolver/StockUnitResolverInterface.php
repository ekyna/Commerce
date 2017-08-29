<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
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
     * Finds the pending stock units.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array|\Ekyna\Component\Commerce\Stock\Model\StockUnitInterface[]
     */
    public function findPending($subjectOrRelative);

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

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
    public function findAvailableOrPendingStockUnitsBySubject(StockSubjectInterface $subject);

    /**
     * Find the stock unit by supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface|null
     */
    public function findOneBySupplierOrderItem(SupplierOrderItemInterface $item);
}

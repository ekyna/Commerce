<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class ProductStockUnitRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitRepository extends ResourceRepository implements StockUnitRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAvailableOrPendingStockUnitsBySubject(StockSubjectInterface $subject)
    {
        // TODO: Implement findAvailableOrPendingStockUnitsBySubject() method.

        return [];
    }

    /**
     * @inheritDoc
     */
    public function findOneBySupplierOrderItem(SupplierOrderItemInterface $item)
    {
        // TODO: Implement findOneBySupplierOrderItem() method.

        return null;
    }
}

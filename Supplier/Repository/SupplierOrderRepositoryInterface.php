<?php

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface SupplierOrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Creates a new supplier order instance.
     *
     * @return SupplierOrderInterface
     */
    public function createNew();

    /**
     * Finds the supplier orders with state 'new' by supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return SupplierOrderInterface[]
     */
    public function findNewBySupplier(SupplierInterface $supplier);

    /**
     * Returns the suppliers expired due.
     *
     * @return float
     */
    public function getSuppliersExpiredDue();

    /**
     * Returns the suppliers fall due.
     *
     * @return float
     */
    public function getSuppliersFallDue();

    /**
     * Returns the carriers expired due.
     *
     * @return float
     */
    public function getCarriersExpiredDue();

    /**
     * Returns the carriers fall due.
     *
     * @return float
     */
    public function getCarriersFallDue();
}

<?php

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface SupplierOrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\SupplierOrderInterface createNew()
 */
interface SupplierOrderRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the supplier orders with state 'new' or 'ordered' by supplier.
     *
     * @param Model\SupplierInterface $supplier
     *
     * @return Model\SupplierOrderInterface[]
     */
    public function findNewBySupplier(Model\SupplierInterface $supplier);

    /**
     * Returns the suppliers expired due orders.
     *
     * @return Model\SupplierOrderInterface[]
     */
    public function findSuppliersExpiredDue();

    /**
     * Returns the suppliers fall due orders.
     *
     * @return Model\SupplierOrderInterface[]
     */
    public function findSuppliersFallDue();

    /**
     * Returns the forwarders expired due orders.
     *
     * @return Model\SupplierOrderInterface[]
     */
    public function findForwardersExpiredDue();

    /**
     * Returns the forwarders fall due orders.
     *
     * @return Model\SupplierOrderInterface[]
     */
    public function findForwardersFallDue();

    /**
     * Returns the suppliers expired due total.
     *
     * @return float
     */
    public function getSuppliersExpiredDue();

    /**
     * Returns the suppliers fall due total.
     *
     * @return float
     */
    public function getSuppliersFallDue();

    /**
     * Returns the forwarders expired due total.
     *
     * @return float
     */
    public function getForwardersExpiredDue();

    /**
     * Returns the forwarders fall due total.
     *
     * @return float
     */
    public function getForwardersFallDue();
}

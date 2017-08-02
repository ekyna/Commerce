<?php

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Interface SupplierOrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderRepositoryInterface
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
}

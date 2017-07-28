<?php

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
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
     * Finds the supplier orders with state 'new' by supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return SupplierOrderInterface[]
     */
    public function findNewBySupplier(SupplierInterface $supplier);
}

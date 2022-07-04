<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SupplierOrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<Model\SupplierOrderInterface>
 */
interface SupplierOrderRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the supplier orders with state 'new' or 'ordered' by supplier.
     *
     * @return array<Model\SupplierOrderInterface>
     */
    public function findNewBySupplier(Model\SupplierInterface $supplier): array;

    /**
     * Returns the suppliers expired due orders.
     *
     * @return array<Model\SupplierOrderInterface>
     */
    public function findSuppliersExpiredDue(): array;

    /**
     * Returns the suppliers fall due orders.
     *
     * @return array<Model\SupplierOrderInterface>
     */
    public function findSuppliersFallDue(): array;

    /**
     * Returns the forwarders expired due orders.
     *
     * @return array<Model\SupplierOrderInterface>
     */
    public function findForwardersExpiredDue(): array;

    /**
     * Returns the forwarders fall due orders.
     *
     * @return array<Model\SupplierOrderInterface>
     */
    public function findForwardersFallDue(): array;

    /**
     * Returns the suppliers expired due total.
     */
    public function getSuppliersExpiredDue(): Decimal;

    /**
     * Returns the suppliers fall due total.
     */
    public function getSuppliersFallDue(): Decimal;

    /**
     * Returns the forwarders expired due total.
     */
    public function getForwardersExpiredDue(): Decimal;

    /**
     * Returns the forwarders fall due total.
     */
    public function getForwardersFallDue(): Decimal;
}

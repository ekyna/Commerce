<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Repository;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SupplierOrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<SupplierOrderInterface>
 */
interface SupplierOrderRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the supplier orders with state 'new' or 'ordered' by supplier.
     *
     * @return array<int, SupplierOrderInterface>
     */
    public function findNewBySupplier(SupplierInterface $supplier): array;

    /**
     * Finds supplier orders by 'ordered at' date.
     *
     * @return array<int, SupplierOrderInterface>
     */
    public function findByOrderAt(DateRange $range, int $page, int $size): array;

    /**
     * Returns the suppliers expired due orders.
     *
     * @return array<int, SupplierOrderInterface>
     */
    public function findSuppliersExpiredDue(): array;

    /**
     * Returns the suppliers fall due orders.
     *
     * @return array<int, SupplierOrderInterface>
     */
    public function findSuppliersFallDue(): array;

    /**
     * Returns the forwarders expired due orders.
     *
     * @return array<int, SupplierOrderInterface>
     */
    public function findForwardersExpiredDue(): array;

    /**
     * Returns the forwarders fall due orders.
     *
     * @return array<int, SupplierOrderInterface>
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

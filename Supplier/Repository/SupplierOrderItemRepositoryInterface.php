<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SupplierOrderItemRepository
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<SupplierOrderItemInterface>
 */
interface SupplierOrderItemRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the pending quantity (i.e., ordered but not confirmed).
     */
    public function getPendingQuantity(SubjectInterface $subject): ?Decimal;

    /**
     * Finds the last ordered supplier order item by subject.
     */
    public function findLatestOrderedBySubject(SubjectInterface $subject): ?SupplierOrderItemInterface;

    /**
     * Finds items from paid but not delivered orders.
     *
     * @return array<SupplierOrderItemInterface>
     */
    public function findPaidAndNotDelivered(): array;
}

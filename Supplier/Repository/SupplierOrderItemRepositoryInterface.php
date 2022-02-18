<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SupplierOrderItemRepository
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderItemRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the last ordered supplier order item by subject.
     *
     * @param SubjectInterface $subject
     *
     * @return SupplierOrderItemInterface|null
     */
    public function findLatestOrderedBySubject(SubjectInterface $subject): ?SupplierOrderItemInterface;

    /**
     * Finds items from paid but not delivered orders.
     *
     * @return array<SupplierOrderItemInterface>
     */
    public function findPaidAndNotDelivered(): array;
}

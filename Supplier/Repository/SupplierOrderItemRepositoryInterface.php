<?php

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

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
     * @return \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface|null
     */
    public function findLatestOrderedBySubject(SubjectInterface $subject);
}

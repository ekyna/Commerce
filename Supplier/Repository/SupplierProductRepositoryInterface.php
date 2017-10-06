<?php

namespace Ekyna\Component\Commerce\Supplier\Repository;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface SupplierProductRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierProductRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the supplier product by supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return SupplierProductInterface[]
     */
    public function findBySupplier(SupplierInterface $supplier);

    /**
     * Finds the supplier products by subject.
     *
     * @param SubjectInterface $subject
     *
     * @return SupplierProductInterface[]
     */
    public function findBySubject(SubjectInterface $subject);

    /**
     * Finds the supplier product by subject and supplier.
     *
     * @param SubjectInterface  $subject
     * @param SupplierInterface $supplier
     * @param SupplierProductInterface $exclude
     *
     * @return SupplierProductInterface
     */
    public function findBySubjectAndSupplier(
        SubjectInterface $subject,
        SupplierInterface $supplier,
        SupplierProductInterface $exclude = null
    );
}

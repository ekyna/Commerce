<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Repository;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SupplierProductRepositoryInterface
 * @package Ekyna\Component\Commerce\Supplier\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierProductRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns whether at least one supplier product exists for the given supplier.
     */
    public function existsForSupplier(SupplierInterface $supplier): bool;

    /**
     * Finds the supplier product by supplier.
     *
     * @return array<SupplierProductInterface>
     */
    public function findBySupplier(SupplierInterface $supplier): array;

    /**
     * Finds the supplier products by subject.
     *
     * @return array<SupplierProductInterface>
     */
    public function findBySubject(SubjectInterface $subject): array;

    /**
     * Returns the estimated date of arrival by subject.
     */
    public function getMinEstimatedDateOfArrivalBySubject(SubjectInterface $subject): ?DateTimeInterface;

    /**
     * Returns the available quantity sum by subject.
     */
    public function getAvailableQuantitySumBySubject(SubjectInterface $subject): Decimal;

    /**
     * Returns the ordered quantity sum by subject.
     */
    public function getOrderedQuantitySumBySubject(SubjectInterface $subject): Decimal;

    /**
     * Finds the supplier product by subject and supplier.
     */
    public function findOneBySubjectAndSupplier(
        SubjectInterface $subject,
        SupplierInterface $supplier,
        SupplierProductInterface $exclude = null
    ): ?SupplierProductInterface;
}

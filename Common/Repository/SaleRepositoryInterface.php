<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SaleRepositoryInterface
 * @package  Ekyna\Component\Commerce\Common\Repository
 * @author   Etienne Dauvergne <contact@ekyna.com>
 *
 * @template S of SaleInterface
 * @implements ResourceRepositoryInterface<S>
 */
interface SaleRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the sale by its id.
     *
     * @return S|null
     */
    public function findOneById(int $id): ?SaleInterface;

    /**
     * Finds the sale by its key.
     *
     * @return S|null
     */
    public function findOneByKey(string $key): ?SaleInterface;

    /**
     * Finds the sale by its number.
     *
     * @return S|null
     */
    public function findOneByNumber(string $number): ?SaleInterface;

    /**
     * Finds the sales by customer, optionally filtered by states.
     *
     * @return array<S>
     */
    public function findByCustomer(CustomerInterface $customer, array $states = [], bool $withChildren = false): array;

    /**
     * Finds the sale by customer and number.
     *
     * @return S|null
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, string $number): ?SaleInterface;

    /**
     * Finds the sales by subject, optionally filtered by states.
     *
     * @return array<S>
     */
    public function findBySubject(SubjectInterface $subject, array $states = []): array;
}

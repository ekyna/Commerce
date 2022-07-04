<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface CustomerRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<CustomerInterface>
 */
interface CustomerRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the customer by its key.
     *
     * @param string $key
     *
     * @return CustomerInterface|null
     */
    public function findOneByKey(string $key): ?CustomerInterface;

    /**
     * Finds the customer by its number.
     *
     * @param string $number
     *
     * @return CustomerInterface|null
     */
    public function findOneByNumber(string $number): ?CustomerInterface;

    /**
     * Finds the customer by its email.
     *
     * @param string $email
     *
     * @return CustomerInterface|null
     */
    public function findOneByEmail(string $email): ?CustomerInterface;

    /**
     * Finds the customers having their birthday today.
     *
     * @return CustomerInterface[]
     */
    public function findWithBirthdayToday(): array;

    /**
     * Finds the customers having a minimum of loyalty points.
     *
     * @param int $points
     *
     * @return CustomerInterface[]
     */
    public function findWithLoyaltyPoints(int $points): array;
}

<?php

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface CustomerRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the customer by its number.
     *
     * @param string $number
     *
     * @return CustomerInterface|null
     */
    public function findOneByNumber(string $number): ?CustomerInterface;

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

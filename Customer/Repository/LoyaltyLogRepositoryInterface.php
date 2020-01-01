<?php

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Entity\LoyaltyLog;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Interface LoyaltyLogRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface LoyaltyLogRepositoryInterface
{
    /**
     * Finds the log by customer and origin.
     *
     * @param CustomerInterface $customer
     * @param string            $origin
     *
     * @return LoyaltyLog|null
     */
    public function findByCustomerAndOrigin(CustomerInterface $customer, string $origin): ?LoyaltyLog;

    /**
     * Finds logs by customer.
     *
     * @param CustomerInterface $customer
     *
     * @return LoyaltyLog[]
     */
    public function findByCustomer(CustomerInterface $customer): array;
}

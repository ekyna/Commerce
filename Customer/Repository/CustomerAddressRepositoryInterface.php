<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface CustomerAddressRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<CustomerAddressInterface>
 */
interface CustomerAddressRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the customer addresses including the parent's ones.
     *
     * @param CustomerInterface $customer
     *
     * @return CustomerAddressInterface[]
     */
    public function findByCustomerAndParents(CustomerInterface $customer): array;

    /**
     * Returns the customer addresses.
     *
     * @param CustomerInterface             $customer
     * @param CustomerAddressInterface|null $exclude
     *
     * @return CustomerAddressInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, CustomerAddressInterface $exclude = null): array;
}

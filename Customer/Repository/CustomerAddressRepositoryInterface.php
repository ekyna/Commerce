<?php

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Interface CustomerAddressRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerAddressRepositoryInterface
{
    /**
     * Returns the customer addresses including the parent's ones.
     *
     * @param CustomerInterface        $customer
     * @param CustomerAddressInterface $exclude
     *
     * @return CustomerAddressInterface[]
     */
    public function findByCustomerAndParents(CustomerInterface $customer);

    /**
     * Returns the customer addresses.
     *
     * @param CustomerInterface        $customer
     * @param CustomerAddressInterface $exclude
     *
     * @return CustomerAddressInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, CustomerAddressInterface $exclude = null);
}

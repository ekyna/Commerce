<?php

namespace Ekyna\Component\Commerce\Customer\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface CustomerAddressRepositoryInterface
 * @package Ekyna\Component\Commerce\Customer\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerContactRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the customer contacts.
     *
     * @param CustomerInterface $customer
     *
     * @return CustomerContactInterface[]
     */
    public function findByCustomer(CustomerInterface $customer): array;
}

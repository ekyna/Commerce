<?php

namespace Ekyna\Component\Commerce\Customer\Model;

/**
 * Interface CustomerEventInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerEventInterface
{
    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer();
}

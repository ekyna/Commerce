<?php

namespace Ekyna\Component\Commerce\Customer\Provider;

use Ekyna\Component\Commerce\Customer\Model;

/**
 * Interface CustomerProviderInterface
 * @package Ekyna\Component\Commerce\Customer\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerProviderInterface
{
    /**
     * Returns whether a customer is available or not.
     *
     * @return bool
     */
    public function hasCustomer();

    /**
     * Returns the customer if available.
     *
     * @return Model\CustomerInterface|null
     */
    public function getCustomer();

    /**
     * Returns the customer's group.
     *
     * @return Model\CustomerGroupInterface
     */
    public function getCustomerGroup();

    /**
     * Resets the customer provider.
     */
    public function reset();
}

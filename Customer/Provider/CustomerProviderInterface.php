<?php

namespace Ekyna\Component\Commerce\Customer\Provider;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

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
     * @return CustomerInterface|null
     */
    public function getCustomer();
}

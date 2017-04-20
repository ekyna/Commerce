<?php

declare(strict_types=1);

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
    public function hasCustomer(): bool;

    /**
     * Returns the customer if available.
     *
     * @return Model\CustomerInterface|null
     */
    public function getCustomer(): ?Model\CustomerInterface;

    /**
     * Returns the customer's group.
     *
     * @return Model\CustomerGroupInterface
     */
    public function getCustomerGroup(): Model\CustomerGroupInterface;

    /**
     * Resets the customer provider.
     */
    public function reset(): void;

    /**
     * Clears the customer provider.
     */
    public function clear(): void;
}

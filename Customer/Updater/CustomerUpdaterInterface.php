<?php

namespace Ekyna\Component\Commerce\Customer\Updater;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Interface CustomerUpdaterInterface
 * @package Ekyna\Component\Commerce\Customer\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerUpdaterInterface
{
    /**
     * Updates the customer outstanding balance.
     *
     * @param CustomerInterface $customer
     * @param float             $amount
     * @param bool              $relative
     *
     * @return float The resulting updated quantity (relative or absolute).
     */
    public function updateOutstandingBalance(CustomerInterface $customer, $amount, $relative = false);
}

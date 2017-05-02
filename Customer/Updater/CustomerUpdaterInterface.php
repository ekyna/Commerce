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
     * Updates the customer's credit balance.
     *
     * @param CustomerInterface $customer
     * @param float             $amount
     * @param bool              $relative
     *
     * @return bool Whether the customer has been changed.
     */
    public function updateCreditBalance(CustomerInterface $customer, $amount, $relative = false);

    /**
     * Updates the customer's outstanding balance.
     *
     * @param CustomerInterface $customer
     * @param float             $amount
     * @param bool              $relative
     *
     * @return float Whether the customer has been changed.
     */
    public function updateOutstandingBalance(CustomerInterface $customer, $amount, $relative = false);
}

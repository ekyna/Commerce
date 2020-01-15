<?php

namespace Ekyna\Component\Commerce\Customer\Updater;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface CustomerUpdaterInterface
 * @package Ekyna\Component\Commerce\Customer\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerUpdaterInterface
{
    /**
     * Handles the payment insertion.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the customer has been changed.
     */
    public function handlePaymentInsert(PaymentInterface $payment): bool;

    /**
     * Handles the payment update.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the customer has been changed.
     */
    public function handlePaymentUpdate(PaymentInterface $payment): bool;

    /**
     * Handles the payment delete.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the customer has been changed.
     */
    public function handlePaymentDelete(PaymentInterface $payment): bool;

    /**
     * Updates the customer's credit balance.
     *
     * @param CustomerInterface $customer
     * @param float             $amount
     * @param bool              $relative
     *
     * @return bool Whether the customer has been changed.
     */
    public function updateCreditBalance(CustomerInterface $customer, $amount, $relative = false): bool;

    /**
     * Updates the customer's outstanding balance.
     *
     * @param CustomerInterface $customer
     * @param float             $amount
     * @param bool              $relative
     *
     * @return bool Whether the customer has been changed.
     */
    public function updateOutstandingBalance(CustomerInterface $customer, $amount, $relative = false): bool;
}

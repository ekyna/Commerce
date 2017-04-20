<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Updater;

use Decimal\Decimal;
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
     * @return bool Whether the customer has been changed.
     */
    public function handlePaymentInsert(PaymentInterface $payment): bool;

    /**
     * Handles the payment update.
     *
     * @return bool Whether the customer has been changed.
     */
    public function handlePaymentUpdate(PaymentInterface $payment): bool;

    /**
     * Handles the payment delete.
     *
     * @return bool Whether the customer has been changed.
     */
    public function handlePaymentDelete(PaymentInterface $payment): bool;

    /**
     * Updates the customer's credit balance.
     *
     * @return bool Whether the customer has been changed.
     */
    public function updateCreditBalance(CustomerInterface $customer, Decimal $amount, bool $relative = false): bool;

    /**
     * Updates the customer's outstanding balance.
     *
     * @return bool Whether the customer has been changed.
     */
    public function updateOutstandingBalance(
        CustomerInterface $customer,
        Decimal           $amount,
        bool              $relative = false
    ): bool;
}

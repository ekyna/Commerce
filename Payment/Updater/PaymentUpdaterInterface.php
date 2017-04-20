<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Updater;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface PaymentUpdaterInterface
 * @package Ekyna\Component\Commerce\Payment\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentUpdaterInterface
{
    /**
     * Updates the payment amount (in payment currency).
     */
    public function updateAmount(PaymentInterface $payment, Decimal $amount): bool;

    /**
     * Updates the payment amount (default currency).
     */
    public function updateRealAmount(PaymentInterface $payment, Decimal $amount): bool;

    /**
     * Fixes the payment amount (after exchange rate or real amount changed).
     */
    public function fixAmount(PaymentInterface $payment): bool;


    /**
     * Fixes the payment real amount (after exchange rate or amount changed).
     */
    public function fixRealAmount(PaymentInterface $payment): bool;
}

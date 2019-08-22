<?php

namespace Ekyna\Component\Commerce\Payment\Updater;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
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
     *
     * @param PaymentInterface $payment
     * @param float            $amount
     *
     * @return bool
     */
    public function updateAmount(PaymentInterface $payment, float $amount): bool;

    /**
     * Updates the payment amount (default currency).
     *
     * @param PaymentInterface $payment
     * @param float            $amount
     *
     * @return bool
     */
    public function updateRealAmount(PaymentInterface $payment, float $amount): bool;

    /**
     * Fixes the payment amount (after exchange rate or real amount changed).
     *
     * @param PaymentInterface $payment
     *
     * @return bool
     */
    public function fixAmount(PaymentInterface $payment): bool;


    /**
     * Fixes the payment real amount (after exchange rate or amount changed).
     *
     * @param PaymentInterface $payment
     *
     * @return bool
     */
    public function fixRealAmount(PaymentInterface $payment): bool;
}

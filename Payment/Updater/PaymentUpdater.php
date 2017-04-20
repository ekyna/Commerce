<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Updater;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class PaymentUpdater
 * @package Ekyna\Component\Commerce\Payment\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentUpdater implements PaymentUpdaterInterface
{
    protected CurrencyConverterInterface $converter;

    public function __construct(CurrencyConverterInterface $currencyConverter)
    {
        $this->converter = $currencyConverter;
    }

    public function updateAmount(PaymentInterface $payment, Decimal $amount): bool
    {
        if (null === $payment->getExchangeRate()) {
            throw new RuntimeException('Payment exchange rate is not defined');
        }

        if (null === $payment->getCurrency()) {
            throw new RuntimeException('Payment currency is not defined.');
        }

        $changed = false;

        if (!$payment->getAmount()->equals($amount)) {
            $payment->setAmount($amount);
            $changed = true;
        }

        return $this->fixRealAmount($payment) || $changed;
    }

    public function updateRealAmount(PaymentInterface $payment, Decimal $amount): bool
    {
        if (null === $payment->getExchangeRate()) {
            throw new RuntimeException('Payment exchange rate is not defined');
        }

        $changed = false;

        if (!$payment->getRealAmount()->equals($amount)) {
            $payment->setRealAmount($amount);
            $changed = true;
        }

        return $this->fixAmount($payment) || $changed;
    }

    public function fixAmount(PaymentInterface $payment): bool
    {
        if (null === $rate = $payment->getExchangeRate()) {
            throw new RuntimeException('Payment exchange rate is not defined');
        }

        if (null === $quote = $payment->getCurrency()) {
            throw new RuntimeException('Payment currency is not defined.');
        }

        $quote = $quote->getCode();

        $amount = $this
            ->converter
            ->convertWithRate($payment->getRealAmount(), $rate, $quote);

        if (!$payment->getAmount()->equals($amount)) {
            $payment->setAmount($amount);

            return true;
        }

        return false;
    }

    public function fixRealAmount(PaymentInterface $payment): bool
    {
        if (null === $rate = $payment->getExchangeRate()) {
            throw new RuntimeException('Payment exchange rate is not defined');
        }

        $base = $this->converter->getDefaultCurrency();

        $amount = $this
            ->converter
            ->convertWithRate($payment->getAmount(), (new Decimal(1))->div($rate), $base, false);

        if (!$payment->getRealAmount()->equals($amount)) {
            $payment->setRealAmount($amount);

            return true;
        }

        return false;
    }
}

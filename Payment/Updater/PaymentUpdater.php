<?php

namespace Ekyna\Component\Commerce\Payment\Updater;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class PaymentUpdater
 * @package Ekyna\Component\Commerce\Payment\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentUpdater implements PaymentUpdaterInterface
{
    /**
     * @var CurrencyConverterInterface
     */
    protected $converter;


    /**
     * Constructor.
     *
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(CurrencyConverterInterface $currencyConverter)
    {
        $this->converter = $currencyConverter;
    }

    /**
     * @inheritDoc
     */
    public function updateAmount(PaymentInterface $payment, float $amount): bool
    {
        if (null === $rate = $payment->getExchangeRate()) {
            throw new RuntimeException("Payment exchange rate is not defined");
        }

        if (null === $quote = $payment->getCurrency()) {
            throw new RuntimeException("Payment currency is not defined.");
        }

        $quote = $quote->getCode();

        $changed = false;

        if (0 !== Money::compare($payment->getAmount(), $amount, $quote)) {
            $payment->setAmount($amount);
            $changed = true;
        }

        $changed |= $this->fixRealAmount($payment);

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function updateRealAmount(PaymentInterface $payment, float $amount): bool
    {
        if (null === $rate = $payment->getExchangeRate()) {
            throw new RuntimeException("Payment exchange rate is not defined");
        }

        $base = $this->converter->getDefaultCurrency();

        $changed = false;

        if (0 !== Money::compare($payment->getRealAmount(), $amount, $base)) {
            $payment->setRealAmount($amount);
            $changed = true;
        }

        $changed |= $this->fixAmount($payment);

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function fixAmount(PaymentInterface $payment): bool
    {
        if (null === $rate = $payment->getExchangeRate()) {
            throw new RuntimeException("Payment exchange rate is not defined");
        }

        if (null === $quote = $payment->getCurrency()) {
            throw new RuntimeException("Payment currency is not defined.");
        }

        $quote = $quote->getCode();

        $amount = $this
            ->converter
            ->convertWithRate($payment->getRealAmount(), $rate, $quote);

        if (0 !== Money::compare($payment->getAmount(), $amount, $quote)) {
            $payment->setAmount($amount);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function fixRealAmount(PaymentInterface $payment): bool
    {
        if (null === $rate = $payment->getExchangeRate()) {
            throw new RuntimeException("Payment exchange rate is not defined");
        }

        $base = $this->converter->getDefaultCurrency();

        $amount = $this
            ->converter
            ->convertWithRate($payment->getAmount(), 1 / $rate, $base, false);

        if (0 !== Money::compare($payment->getRealAmount(), $amount, $base)) {
            $payment->setRealAmount($amount);

            return true;
        }

        return false;
    }
}

<?php

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;

/**
 * Interface CurrencyProviderInterface
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyProviderInterface
{
    /**
     * Returns the available currencies.
     *
     * @return array
     */
    public function getAvailableCurrencies(): array;

    /**
     * Returns the fallback currency.
     *
     * @return string
     */
    public function getFallbackCurrency(): string;

    /**
     * Returns the current currency.
     *
     * @return string
     */
    public function getCurrentCurrency(): string;

    /**
     * Sets the current currency.
     *
     * @param string $currency
     *
     * @return CurrencyProviderInterface
     */
    public function setCurrentCurrency(string $currency): CurrencyProviderInterface;

    /**
     * Returns the currency (entity) by its code, or the current one if no code is provided.
     *
     * @param string $code
     *
     * @return CurrencyInterface
     */
    public function getCurrency(string $code = null): CurrencyInterface;
}

<?php

namespace Ekyna\Component\Commerce\Common\Currency;

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
    public function getAvailableCurrencies();

    /**
     * Returns the fallback currency.
     *
     * @return string
     */
    public function getFallbackCurrency();

    /**
     * Returns the current currency.
     *
     * @return string
     */
    public function getCurrentCurrency();

    /**
     * Sets the current currency.
     *
     * @param string $currency
     *
     * @return CurrencyProviderInterface
     */
    public function setCurrentCurrency(string $currency);

    /**
     * Returns the currency (entity) by its code, or the current one if no code is provided.
     *
     * @param string $code
     *
     * @return \Ekyna\Component\Commerce\Common\Model\CurrencyInterface
     */
    public function getCurrency(string $code = null);
}

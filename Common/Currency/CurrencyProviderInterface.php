<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;

/**
 * Interface CurrencyProviderInterface
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyProviderInterface
{
    /**
     * Returns the available currencies.
     */
    public function getAvailableCurrencies(): array;

    /**
     * Returns the fallback currency.
     */
    public function getFallbackCurrency(): string;

    /**
     * Returns the current currency.
     */
    public function getCurrentCurrency(): string;

    /**
     * Sets the current currency.
     *
     * @param string|CurrencyInterface $currency
     */
    public function setCurrency($currency): CurrencyProviderInterface;

    /**
     * Returns the currency (entity) by its code, or the current one if no code is provided.
     */
    public function getCurrency(string $code = null): CurrencyInterface;

    /**
     * Returns the currency repository.
     */
    public function getCurrencyRepository(): CurrencyRepositoryInterface;
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Context;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

/**
 * Interface ContextProviderInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ContextProviderInterface
{
    /**
     * Returns the customer provider.
     */
    public function getCustomerProvider(): CustomerProviderInterface;

    /**
     * Returns the cart provider.
     */
    public function getCartProvider(): CartProviderInterface;

    /**
     * Returns the local provider.
     */
    public function getLocalProvider(): LocaleProviderInterface;

    /**
     * Returns the currency provider.
     */
    public function getCurrencyProvider(): CurrencyProviderInterface;

    /**
     * Returns the country provider.
     */
    public function getCountryProvider(): CountryProviderInterface;

    /**
     * Returns the context.
     */
    public function getContext(SaleInterface $sale = null): ContextInterface;

    /**
     * Sets the context and fills empty properties with default values.
     *
     * @param ContextInterface|SaleInterface $contextOrSale
     */
    public function setContext($contextOrSale): ContextProviderInterface;

    /**
     * Change the currency and the country.
     *
     * @param CurrencyInterface|string|null $currency
     * @param CountryInterface|string|null  $country
     */
    public function changeCurrencyAndCountry(
        $currency = null,
        $country = null,
        string $locale = null
    ): ContextProviderInterface;

    /**
     * Clears the cached context.
     */
    public function onClear(): void;
}

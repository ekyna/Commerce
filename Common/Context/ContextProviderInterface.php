<?php

namespace Ekyna\Component\Commerce\Common\Context;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
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
     *
     * @return CustomerProviderInterface
     */
    public function getCustomerProvider(): CustomerProviderInterface;

    /**
     * Returns the cart provider.
     *
     * @return CartProviderInterface
     */
    public function getCartProvider(): CartProviderInterface;

    /**
     * Returns the local provider.
     *
     * @return LocaleProviderInterface
     */
    public function getLocalProvider(): LocaleProviderInterface;

    /**
     * Returns the currency provider.
     *
     * @return CurrencyProviderInterface
     */
    public function getCurrencyProvider(): CurrencyProviderInterface;

    /**
     * Returns the country provider.
     *
     * @return CountryProviderInterface
     */
    public function getCountryProvider(): CountryProviderInterface;

    /**
     * Returns the context.
     *
     * @param SaleInterface $sale The sale to build the context for, if any.
     *
     * @return ContextInterface
     */
    public function getContext(SaleInterface $sale = null): ContextInterface;

    /**
     * Sets the context and fills empty properties with default values.
     *
     * @param ContextInterface|SaleInterface $contextOrSale
     *
     * @return ContextProviderInterface
     */
    public function setContext($contextOrSale): ContextProviderInterface;

    /**
     * Clears the cached context.
     *
     * @return ContextProviderInterface
     */
    public function clearContext(): ContextProviderInterface;

    /**
     * Change the currency and the country.
     *
     * @param \Ekyna\Component\Commerce\Common\Model\CurrencyInterface|string|null $currency
     * @param \Ekyna\Component\Commerce\Common\Model\CountryInterface|string|null  $country
     *
     * @return ContextProviderInterface
     */
    public function changeCurrencyAndCountry($currency = null, $country = null): ContextProviderInterface;
}

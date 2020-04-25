<?php

namespace Ekyna\Component\Commerce\Common\Context;

use DateTime;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Interface ContextInterface
 * @package Ekyna\Component\Commerce\Common\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ContextInterface
{
    /**
     * Returns the customer group.
     *
     * @return CustomerGroupInterface|null
     */
    public function getCustomerGroup(): ?CustomerGroupInterface;

    /**
     * Sets the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|ContextInterface
     */
    public function setCustomerGroup(CustomerGroupInterface $group): ContextInterface;

    /**
     * Returns the invoice country.
     *
     * @return CountryInterface|null
     */
    public function getInvoiceCountry(): ?CountryInterface;

    /**
     * Sets the invoice country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ContextInterface
     */
    public function setInvoiceCountry(CountryInterface $country): ContextInterface;

    /**
     * Returns the delivery country.
     *
     * @return CountryInterface|null
     */
    public function getDeliveryCountry(): ?CountryInterface;

    /**
     * Sets the delivery country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ContextInterface
     */
    public function setDeliveryCountry(CountryInterface $country): ContextInterface;

    /**
     * Returns the shipping country.
     *
     * @return CountryInterface|null
     */
    public function getShippingCountry(): ?CountryInterface;

    /**
     * Sets the shipping country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ContextInterface
     */
    public function setShippingCountry(CountryInterface $country): ContextInterface;

    /**
     * Returns the currency.
     *
     * @return CurrencyInterface|null
     */
    public function getCurrency(): ?CurrencyInterface;

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|ContextInterface
     */
    public function setCurrency(CurrencyInterface $currency): ContextInterface;

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale(): ?string;

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return $this|ContextInterface
     */
    public function setLocale(string $locale): ContextInterface;

    /**
     * Returns the VAT display mode.
     *
     * @return string
     */
    public function getVatDisplayMode(): ?string;

    /**
     * Sets the VAT display mode.
     *
     * @param string $mode
     *
     * @return $this|ContextInterface
     */
    public function setVatDisplayMode(string $mode): ContextInterface;

    /**
     * Returns whether the context is business.
     *
     * @return bool
     */
    public function isBusiness(): bool;

    /**
     * Sets whether the context is business.
     *
     * @param bool $business
     *
     * @return $this|ContextInterface
     */
    public function setBusiness(bool $business): ContextInterface;

    /**
     * Returns the whether the context is tax exempt.
     *
     * @return bool
     */
    public function isTaxExempt(): bool;

    /**
     * Sets whether the context is tax exempt.
     *
     * @param bool $exempt
     *
     * @return $this|ContextInterface
     */
    public function setTaxExempt(bool $exempt): ContextInterface;

    /**
     * Returns the date.
     *
     * @return DateTime
     */
    public function getDate(): DateTime;

    /**
     * Sets the date.
     *
     * @param DateTime $date
     *
     * @return $this|ContextInterface
     */
    public function setDate(DateTime $date): ContextInterface;

    /**
     * Returns whether the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool;

    /**
     * Sets whether the user is an admin.
     *
     * @param bool $admin
     *
     * @return Context
     */
    public function setAdmin(bool $admin): ContextInterface;

    /**
     * Returns whether prices should be displayed "all taxes included".
     *
     * @return bool
     */
    public function isAtiDisplayMode(): bool;
}

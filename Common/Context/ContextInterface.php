<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Context;

use DateTimeInterface;
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
     */
    public function getCustomerGroup(): ?CustomerGroupInterface;

    /**
     * Sets the customer group.
     */
    public function setCustomerGroup(CustomerGroupInterface $group): ContextInterface;

    /**
     * Returns the invoice country.
     */
    public function getInvoiceCountry(): ?CountryInterface;

    /**
     * Sets the invoice country.
     */
    public function setInvoiceCountry(CountryInterface $country): ContextInterface;

    /**
     * Returns the delivery country.
     */
    public function getDeliveryCountry(): ?CountryInterface;

    /**
     * Sets the delivery country.
     */
    public function setDeliveryCountry(CountryInterface $country): ContextInterface;

    /**
     * Returns the shipping country.
     */
    public function getShippingCountry(): ?CountryInterface;

    /**
     * Sets the shipping country.
     */
    public function setShippingCountry(CountryInterface $country): ContextInterface;

    /**
     * Returns the currency.
     */
    public function getCurrency(): ?CurrencyInterface;

    /**
     * Sets the currency.
     */
    public function setCurrency(CurrencyInterface $currency): ContextInterface;

    /**
     * Returns the locale.
     */
    public function getLocale(): ?string;

    /**
     * Sets the locale.
     */
    public function setLocale(string $locale): ContextInterface;

    /**
     * Returns the VAT display mode.
     */
    public function getVatDisplayMode(): ?string;

    /**
     * Sets the VAT display mode.
     */
    public function setVatDisplayMode(?string $mode): ContextInterface;

    /**
     * Returns whether the context is business.
     */
    public function isBusiness(): bool;

    /**
     * Sets whether the context is business.
     */
    public function setBusiness(bool $business): ContextInterface;

    /**
     * Returns the whether the context is tax exempt.
     */
    public function isTaxExempt(): bool;

    /**
     * Sets whether the context is tax exempt.
     */
    public function setTaxExempt(bool $exempt): ContextInterface;

    /**
     * Returns the date.
     */
    public function getDate(): DateTimeInterface;

    /**
     * Sets the date.
     */
    public function setDate(DateTimeInterface $date): ContextInterface;

    /**
     * Returns whether the user is an admin.
     */
    public function isAdmin(): bool;

    /**
     * Sets whether the user is an admin.
     */
    public function setAdmin(bool $admin): ContextInterface;

    /**
     * Returns whether prices should be displayed "all taxes included".
     */
    public function isAtiDisplayMode(): bool;
}

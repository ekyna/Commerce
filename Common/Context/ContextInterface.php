<?php

namespace Ekyna\Component\Commerce\Common\Context;

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
     * @return CustomerGroupInterface
     */
    public function getCustomerGroup();

    /**
     * Sets the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|ContextInterface
     */
    public function setCustomerGroup(CustomerGroupInterface $group);

    /**
     * Returns the invoice country.
     *
     * @return CountryInterface
     */
    public function getInvoiceCountry();

    /**
     * Sets the invoice country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ContextInterface
     */
    public function setInvoiceCountry(CountryInterface $country);

    /**
     * Returns the delivery country.
     *
     * @return CountryInterface
     */
    public function getDeliveryCountry();

    /**
     * Sets the delivery country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ContextInterface
     */
    public function setDeliveryCountry(CountryInterface $country);

    /**
     * Returns the currency.
     *
     * @return CurrencyInterface
     */
    public function getCurrency();

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|ContextInterface
     */
    public function setCurrency(CurrencyInterface $currency);

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale();

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return $this|ContextInterface
     */
    public function setLocale(string $locale);

    /**
     * Returns the VAT display mode.
     *
     * @return string
     */
    public function getVatDisplayMode();

    /**
     * Sets the VAT display mode.
     *
     * @param string $mode
     *
     * @return Context
     */
    public function setVatDisplayMode($mode);

    /**
     * Returns the date.
     *
     * @return \DateTime
     */
    public function getDate();

    /**
     * Sets the date.
     *
     * @param \DateTime $date
     *
     * @return Context
     */
    public function setDate(\DateTime $date);

    /**
     * Returns whether prices should be displayed "all taxes included".
     *
     * @return bool
     */
    public function isAtiDisplayMode();
}

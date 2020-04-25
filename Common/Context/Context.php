<?php

namespace Ekyna\Component\Commerce\Common\Context;

use DateTime;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;

/**
 * Class Context
 * @package Ekyna\Component\Commerce\Common\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Context implements ContextInterface
{
    /**
     * @var CustomerGroupInterface
     */
    protected $customerGroup;

    /**
     * @var CountryInterface
     */
    protected $invoiceCountry;

    /**
     * @var CountryInterface
     */
    protected $deliveryCountry;

    /**
     * @var CountryInterface
     */
    protected $shippingCountry;

    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $vatDisplayMode;

    /**
     * @var bool
     */
    protected $business;

    /**
     * @var bool
     */
    protected $taxExempt;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var bool
     */
    protected $admin;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->business = false;
        $this->taxExempt = false;
        $this->date = new DateTime();
        $this->admin = false;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroup(): ?CustomerGroupInterface
    {
        return $this->customerGroup;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroup(CustomerGroupInterface $group): ContextInterface
    {
        $this->customerGroup = $group;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceCountry(): ?CountryInterface
    {
        return $this->invoiceCountry;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceCountry(CountryInterface $country): ContextInterface
    {
        $this->invoiceCountry = $country;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryCountry(): ?CountryInterface
    {
        return $this->deliveryCountry;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryCountry(CountryInterface $country): ContextInterface
    {
        $this->deliveryCountry = $country;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippingCountry(): ?CountryInterface
    {
        return $this->shippingCountry;
    }

    /**
     * @inheritdoc
     */
    public function setShippingCountry(CountryInterface $country): ContextInterface
    {
        $this->shippingCountry = $country;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency(): ?CurrencyInterface
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(CurrencyInterface $currency): ContextInterface
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @inheritdoc
     */
    public function setLocale(string $locale): ContextInterface
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVatDisplayMode(): ?string
    {
        return $this->vatDisplayMode;
    }

    /**
     * @inheritdoc
     */
    public function setVatDisplayMode(string $mode): ContextInterface
    {
        $this->vatDisplayMode = $mode;

        return $this;
    }

    /**
     * Returns whether the context is business.
     *
     * @return bool
     */
    public function isBusiness(): bool
    {
        return $this->business;
    }

    /**
     * Sets whether the context is business.
     *
     * @param bool $business
     *
     * @return Context
     */
    public function setBusiness(bool $business): ContextInterface
    {
        $this->business = $business;

        return $this;
    }

    /**
     * Returns the whether the context is tax exempt.
     *
     * @return bool
     */
    public function isTaxExempt(): bool
    {
        return $this->taxExempt;
    }

    /**
     * Sets whether the context is tax exempt.
     *
     * @param bool $exempt
     *
     * @return Context
     */
    public function setTaxExempt(bool $exempt): ContextInterface
    {
        $this->taxExempt = $exempt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @inheritdoc
     */
    public function setDate(DateTime $date): ContextInterface
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * @inheritdoc
     */
    public function setAdmin(bool $admin): ContextInterface
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isAtiDisplayMode(): bool
    {
        return $this->vatDisplayMode === VatDisplayModes::MODE_ATI;
    }
}

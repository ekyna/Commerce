<?php

namespace Ekyna\Component\Commerce\Common\Context;

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
     * @var \DateTime
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
        $this->date = new \DateTime();
        $this->admin = false;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroup(CustomerGroupInterface $group)
    {
        $this->customerGroup = $group;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceCountry()
    {
        return $this->invoiceCountry;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceCountry(CountryInterface $country)
    {
        $this->invoiceCountry = $country;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryCountry()
    {
        return $this->deliveryCountry;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryCountry(CountryInterface $country)
    {
        $this->deliveryCountry = $country;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(CurrencyInterface $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @inheritdoc
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVatDisplayMode()
    {
        return $this->vatDisplayMode ? $this->vatDisplayMode : $this->customerGroup->getVatDisplayMode();
    }

    /**
     * @inheritdoc
     */
    public function setVatDisplayMode(string $mode)
    {
        $this->vatDisplayMode = $mode;

        return $this;
    }

    /**
     * Returns whether the context is business.
     *
     * @return bool
     */
    public function isBusiness()
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
    public function setBusiness(bool $business)
    {
        $this->business = $business;

        return $this;
    }

    /**
     * Returns the whether the context is tax exempt.
     *
     * @return bool
     */
    public function isTaxExempt()
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
    public function setTaxExempt(bool $exempt)
    {
        $this->taxExempt = $exempt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @inheritdoc
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAdmin()
    {
        return $this->admin;
    }

    /**
     * @inheritdoc
     */
    public function setAdmin(bool $admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isAtiDisplayMode()
    {
        return $this->vatDisplayMode === VatDisplayModes::MODE_ATI;
    }
}

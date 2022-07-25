<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Context;

use DateTime;
use DateTimeInterface;
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
    protected ?CustomerGroupInterface $customerGroup   = null;
    protected ?CountryInterface       $invoiceCountry  = null;
    protected ?CountryInterface       $deliveryCountry = null;
    protected ?CountryInterface       $shippingCountry = null;
    protected ?CurrencyInterface      $currency        = null;
    protected ?string                 $locale          = null;
    protected ?string                 $vatDisplayMode  = null;
    protected bool                    $business        = false;
    protected bool                    $taxExempt       = false;
    protected DateTimeInterface       $date;
    protected bool                    $admin           = false;


    public function __construct()
    {
        $this->date = new DateTime();
    }

    public function getCustomerGroup(): ?CustomerGroupInterface
    {
        return $this->customerGroup;
    }

    public function setCustomerGroup(CustomerGroupInterface $group): ContextInterface
    {
        $this->customerGroup = $group;

        return $this;
    }

    public function getInvoiceCountry(): ?CountryInterface
    {
        return $this->invoiceCountry;
    }

    public function setInvoiceCountry(CountryInterface $country): ContextInterface
    {
        $this->invoiceCountry = $country;

        return $this;
    }

    public function getDeliveryCountry(): ?CountryInterface
    {
        return $this->deliveryCountry;
    }

    public function setDeliveryCountry(CountryInterface $country): ContextInterface
    {
        $this->deliveryCountry = $country;

        return $this;
    }

    public function getShippingCountry(): ?CountryInterface
    {
        return $this->shippingCountry;
    }

    public function setShippingCountry(CountryInterface $country): ContextInterface
    {
        $this->shippingCountry = $country;

        return $this;
    }

    public function getCurrency(): ?CurrencyInterface
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyInterface $currency): ContextInterface
    {
        $this->currency = $currency;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): ContextInterface
    {
        $this->locale = $locale;

        return $this;
    }

    public function getVatDisplayMode(): ?string
    {
        return $this->vatDisplayMode;
    }

    public function setVatDisplayMode(?string $mode): ContextInterface
    {
        $this->vatDisplayMode = $mode;

        return $this;
    }

    public function isBusiness(): bool
    {
        return $this->business;
    }

    public function setBusiness(bool $business): ContextInterface
    {
        $this->business = $business;

        return $this;
    }

    public function isTaxExempt(): bool
    {
        return $this->taxExempt;
    }

    public function setTaxExempt(bool $exempt): ContextInterface
    {
        $this->taxExempt = $exempt;

        return $this;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): ContextInterface
    {
        $this->date = $date;

        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): ContextInterface
    {
        $this->admin = $admin;

        return $this;
    }

    public function isAtiDisplayMode(): bool
    {
        return $this->vatDisplayMode === VatDisplayModes::MODE_ATI;
    }
}

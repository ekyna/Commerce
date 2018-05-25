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
    protected $taxExempt; // TODO if sale.isTaxExempt() || sale.isSample()

    /**
     * @var \DateTime
     */
    protected $date;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
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
    public function setVatDisplayMode($mode)
    {
        $this->vatDisplayMode = $mode;

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
     * @inheritDoc
     */
    public function isAtiDisplayMode()
    {
        return $this->vatDisplayMode === VatDisplayModes::MODE_ATI;
    }
}

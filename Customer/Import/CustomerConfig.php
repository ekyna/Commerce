<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Import;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermInterface;
use Ekyna\Component\Resource\Import\AbstractConfig;

/**
 * Class CustomerConfig
 * @package Ekyna\Component\Commerce\Customer\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerConfig extends AbstractConfig
{
    protected static array $definitions = [
        'company'              => ['field.company', 'EkynaUi'],
        'companyNumber'        => ['customer.field.company_number', 'EkynaCommerce'],
        'email'                => ['field.email', 'EkynaUi'],
        'gender'               => ['field.gender', 'EkynaUi'],
        'firstName'            => ['field.first_name', 'EkynaUi'],
        'lastName'             => ['field.last_name', 'EkynaUi'],
        'phone'                => ['field.phone', 'EkynaUi'],
        'mobile'               => ['field.mobile', 'EkynaUi'],
        'parent'               => ['customer.field.parent', 'EkynaCommerce'],
        'customerGroup'        => ['customer_group.label.singular', 'EkynaCommerce'],
        'defaultPaymentMethod' => ['customer.field.default_payment_method', 'EkynaCommerce'],
        'paymentTerm'          => ['payment_term.label.singular', 'EkynaCommerce'],
        'vatNumber'            => ['pricing.field.vat_number', 'EkynaCommerce'],
        'outstandingLimit'     => ['sale.field.outstanding_limit', 'EkynaCommerce'],
        'currency'             => ['currency.label.singular', 'EkynaCommerce'],
        'locale'               => ['field.locale', 'EkynaUi'],
    ];

    private ?string                 $defaultGender        = null;
    private ?CustomerInterface      $defaultParent        = null;
    private ?CustomerGroupInterface $defaultGroup         = null;
    private ?PaymentMethodInterface $defaultPaymentMethod = null;
    private ?PaymentTermInterface   $defaultPaymentTerm   = null;
    private ?CurrencyInterface      $defaultCurrency      = null;
    private ?string                 $defaultLocale        = null;
    private ?CountryInterface       $defaultCountry       = null;
    private bool                    $skipDuplicateEmail   = false;

    public function getDefaultGender(): ?string
    {
        return $this->defaultGender;
    }

    public function setDefaultGender(?string $defaultGender): CustomerConfig
    {
        $this->defaultGender = $defaultGender;

        return $this;
    }

    public function getDefaultParent(): ?CustomerInterface
    {
        return $this->defaultParent;
    }

    public function setDefaultParent(?CustomerInterface $defaultParent): CustomerConfig
    {
        $this->defaultParent = $defaultParent;

        return $this;
    }

    public function getDefaultGroup(): ?CustomerGroupInterface
    {
        return $this->defaultGroup;
    }

    public function setDefaultGroup(?CustomerGroupInterface $defaultGroup): CustomerConfig
    {
        $this->defaultGroup = $defaultGroup;

        return $this;
    }

    public function getDefaultPaymentMethod(): ?PaymentMethodInterface
    {
        return $this->defaultPaymentMethod;
    }

    public function setDefaultPaymentMethod(?PaymentMethodInterface $defaultPaymentMethod): CustomerConfig
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;

        return $this;
    }

    public function getDefaultPaymentTerm(): ?PaymentTermInterface
    {
        return $this->defaultPaymentTerm;
    }

    public function setDefaultPaymentTerm(?PaymentTermInterface $defaultPaymentTerm): CustomerConfig
    {
        $this->defaultPaymentTerm = $defaultPaymentTerm;

        return $this;
    }

    public function getDefaultCurrency(): ?CurrencyInterface
    {
        return $this->defaultCurrency;
    }

    public function setDefaultCurrency(?CurrencyInterface $defaultCurrency): CustomerConfig
    {
        $this->defaultCurrency = $defaultCurrency;

        return $this;
    }

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    public function setDefaultLocale(?string $defaultLocale): CustomerConfig
    {
        $this->defaultLocale = $defaultLocale;

        return $this;
    }

    public function getDefaultCountry(): ?CountryInterface
    {
        return $this->defaultCountry;
    }

    public function setDefaultCountry(?CountryInterface $defaultCountry): CustomerConfig
    {
        $this->defaultCountry = $defaultCountry;

        return $this;
    }

    public function isSkipDuplicateEmail(): bool
    {
        return $this->skipDuplicateEmail;
    }

    public function setSkipDuplicateEmail(bool $skipDuplicateEmail): CustomerConfig
    {
        $this->skipDuplicateEmail = $skipDuplicateEmail;

        return $this;
    }
}

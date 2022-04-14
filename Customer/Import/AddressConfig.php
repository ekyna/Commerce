<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Import;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Import\AbstractConfig;

/**
 * Class AddressConfig
 * @package Ekyna\Component\Commerce\Customer\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressConfig extends AbstractConfig
{
    protected static array $definitions = [
        'company'    => ['field.company', 'EkynaUi'],
        'gender'     => ['field.gender', 'EkynaUi'],
        'firstName'  => ['field.first_name', 'EkynaUi'],
        'lastName'   => ['field.last_name', 'EkynaUi'],
        'street'     => ['field.street', 'EkynaUi'],
        'complement' => ['address.field.complement', 'EkynaCommerce'],
        'supplement' => ['address.field.supplement', 'EkynaCommerce'],
        'extra'      => ['address.field.extra', 'EkynaCommerce'],
        'postalCode' => ['field.postal_code', 'EkynaUi'],
        'city'       => ['field.city', 'EkynaUi'],
        'country'    => ['country.label.singular', 'EkynaCommerce'],
        // TODO 'state',
        'phone'      => ['field.phone', 'EkynaUi'],
        'mobile'     => ['field.mobile', 'EkynaUi'],
        'digicode1'  => ['address.field.digicode1', 'EkynaCommerce'],
        'digicode2'  => ['address.field.digicode2', 'EkynaCommerce'],
        'intercom'   => ['address.field.intercom', 'EkynaCommerce'],
    ];

    private ?string            $defaultGender   = null;
    private ?CountryInterface  $defaultCountry  = null;
    private ?CustomerInterface $customer        = null;
    private bool               $invoiceDefault  = false;
    private bool               $deliveryDefault = false;

    public function getDefaultGender(): ?string
    {
        return $this->defaultGender;
    }

    public function setDefaultGender(?string $defaultGender): AddressConfig
    {
        $this->defaultGender = $defaultGender;

        return $this;
    }

    public function getDefaultCountry(): ?CountryInterface
    {
        return $this->defaultCountry;
    }

    public function setDefaultCountry(?CountryInterface $defaultCountry): AddressConfig
    {
        $this->defaultCountry = $defaultCountry;

        return $this;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): AddressConfig
    {
        $this->customer = $customer;

        return $this;
    }

    public function isInvoiceDefault(): bool
    {
        return $this->invoiceDefault;
    }

    public function setInvoiceDefault(bool $invoiceDefault): AddressConfig
    {
        $this->invoiceDefault = $invoiceDefault;

        return $this;
    }

    public function isDeliveryDefault(): bool
    {
        return $this->deliveryDefault;
    }

    public function setDeliveryDefault(bool $deliveryDefault): AddressConfig
    {
        $this->deliveryDefault = $deliveryDefault;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Import;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Common\Util\AddressUtil;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Resource\Exception\ImportException;
use Ekyna\Component\Resource\Import\AbstractConsumer;
use Ekyna\Component\Resource\Model\ResourceInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

use function in_array;

/**
 * Class AddressConsumer
 * @package Ekyna\Component\Commerce\Customer\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @property CustomerAddressInterface $resource
 * @property AddressConfig            $config
 * @method   AddressConfig            getConfig()
 */
class AddressConsumer extends AbstractConsumer
{
    private PhoneNumberUtil $phoneNumberUtil;

    private ?CustomerConsumer           $customerConsumer  = null;
    private ?CountryRepositoryInterface $countryRepository = null;

    public function __construct(PhoneNumberUtil $phoneNumberUtil)
    {
        parent::__construct(new AddressConfig());

        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function setCustomerConsumer(?CustomerConsumer $consumer): void
    {
        $this->customerConsumer = $consumer;
    }

    protected function createResource(): ?ResourceInterface
    {
        if ($this->customerConsumer) {
            if (null === $customer = $this->customerConsumer->getResource()) {
                return null;
            }
        } else {
            $customer = $this->config->getCustomer();
        }

        /** @var CustomerAddressInterface $address */
        $address = $this
            ->factoryFactory
            ->getFactory(CustomerAddressInterface::class)
            ->create();

        $address->setCustomer($customer);
        $address->setGender($this->config->getDefaultGender());
        $address->setCountry($this->config->getDefaultCountry());
        $address->setInvoiceDefault($this->config->isInvoiceDefault());
        $address->setDeliveryDefault($this->config->isDeliveryDefault());

        return $address;
    }

    /**
     * @inheritDoc
     */
    protected function transformValue(string $property, $value)
    {
        if (empty($value)) {
            return null;
        }

        if ($property === 'country') {
            $country = $this->getCountryRepository()->findOneByCode($value);

            if (null === $country) {
                throw new ImportException("Unknown '$value' country");
            }

            return $country;
        }

        /* TODO if ($property === 'state') {
            $state = $this->getStateRepository()->findOneByCode($value);

            if (null === $state) {
                throw new ImportException("Unknown '$value' state");
            }

            return $state;
        }*/

        if (in_array($property, ['phone', 'mobile'], true)) {
            try {
                $value = $this
                    ->phoneNumberUtil
                    ->parse($value, $this->config->getDefaultCountry()->getCode());
            } catch (NumberParseException $exception) {
                throw new ImportException("Invalid phone number '$value'", 0, $exception);
            }

            return $value;
        }

        return $value;
    }

    protected function validateResource(): bool
    {
        if (empty($this->resource->getFirstName()) || empty($this->resource->getLastName())) {
            $this->resource->clearIdentity();
        }

        if ($customer = $this->resource->getCustomer()) {
            foreach ($customer->getAddresses() as $address) {
                if ($this->resource === $address) {
                    continue;
                }

                if (AddressUtil::equals($this->resource, $address)) {
                    return false;
                }
            }
        }

        return parent::validateResource();
    }

    private function getCountryRepository(): CountryRepositoryInterface
    {
        if ($this->countryRepository) {
            return $this->countryRepository;
        }

        return $this->countryRepository = $this->repositoryFactory->getRepository(CountryInterface::class);
    }
}

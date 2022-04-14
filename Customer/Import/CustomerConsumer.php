<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Import;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Ekyna\Component\Resource\Exception\ImportException;
use Ekyna\Component\Resource\Import\AbstractConsumer;
use Ekyna\Component\Resource\Model\ResourceInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

use function in_array;

/**
 * Class CustomerConsumer
 * @package Ekyna\Component\Commerce\Customer\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @property CustomerInterface $resource
 * @property CustomerConfig    $config
 * @method   CustomerConfig    getConfig()
 */
class CustomerConsumer extends AbstractConsumer
{
    private PhoneNumberUtil $phoneNumberUtil;

    private ?CountryRepositoryInterface  $countryRepository  = null;
    private ?CurrencyRepositoryInterface $currencyRepository = null;
    private ?CustomerRepositoryInterface $customerRepository = null;

    private array $emails = [];

    public function __construct(PhoneNumberUtil $phoneNumberUtil)
    {
        parent::__construct(new CustomerConfig());

        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    protected function createResource(): ?ResourceInterface
    {
        /** @var CustomerInterface $customer */
        $customer = $this
            ->factoryFactory
            ->getFactory('ekyna_commerce.customer') // TODO Use CustomerInterface::class instead of resource string
            ->create();

        $customer->setGender($this->config->getDefaultGender());
        $customer->setLocale($this->config->getDefaultLocale());
        if ($parent = $this->config->getDefaultParent()) {
            $customer->setParent($parent);
        } else {
            $customer->setCustomerGroup($this->config->getDefaultGroup());
        }
        $customer->setCurrency($this->config->getDefaultCurrency());
        $customer->setDefaultPaymentMethod($this->config->getDefaultPaymentMethod());
        $customer->setPaymentTerm($this->config->getDefaultPaymentTerm());

        return $customer;
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

        if ($property === 'currency') {
            $currency = $this->getCurrencyRepository()->findOneByCode($value);

            if (null === $currency) {
                throw new ImportException("Unknown '$value' currency");
            }

            return $currency;
        }

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
        $skip = $this->config->isSkipDuplicateEmail();
        if (!empty($email = $this->resource->getEmail())) {
            if ($skip && $this->getCustomerRepository()->findOneByEmail($email)) {
                return false;
            }

            if (in_array($email, $this->emails, true)) {
                if ($skip) {
                    return false;
                }

                throw new ImportException("Duplicate email '$email'.");
            }

            $this->emails[] = $email;
        }

        if (empty($this->resource->getFirstName()) || empty($this->resource->getLastName())) {
            $this->resource->clearIdentity();
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

    private function getCurrencyRepository(): CurrencyRepositoryInterface
    {
        if ($this->currencyRepository) {
            return $this->currencyRepository;
        }

        return $this->currencyRepository = $this->repositoryFactory->getRepository(CurrencyInterface::class);
    }

    private function getCustomerRepository(): CustomerRepositoryInterface
    {
        if ($this->customerRepository) {
            return $this->customerRepository;
        }

        return $this->customerRepository = $this->repositoryFactory->getRepository(CustomerInterface::class);
    }
}

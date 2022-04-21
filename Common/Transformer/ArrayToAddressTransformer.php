<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Model\Address;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use function in_array;

/**
 * Class ArrayToAddressTransformer
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ArrayToAddressTransformer
{
    private const PROPERTIES = [
        'company'     => null,
        'gender'      => null,
        'firstName'   => 'first_name',
        'lastName'    => 'last_name',
        'street'      => null,
        'supplement'  => null,
        'complement'  => null,
        'extra'       => null,
        'postalCode'  => 'postal_code',
        'city'        => null,
        'country'     => null,
        'state'       => null,
        'phone'       => null,
        'mobile'      => null,
        'digicode1'   => null,
        'digicode2'   => null,
        'intercom'    => null,
        'information' => null,
        'latitude'    => null,
        'longitude'   => null,
    ];

    protected CountryRepositoryInterface $countryRepository;
    protected PhoneNumberUtil            $phoneNumberUtil;
    protected PropertyAccessor           $accessor;

    public function __construct(CountryRepositoryInterface $countryRepository, PhoneNumberUtil $phoneNumberUtil)
    {
        $this->countryRepository = $countryRepository;
        $this->phoneNumberUtil = $phoneNumberUtil;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function getCountryRepository(): CountryRepositoryInterface
    {
        return $this->countryRepository;
    }

    protected function getProperties(): array
    {
        return self::PROPERTIES;
    }

    /**
     * Transforms array into address.
     */
    public function transformArray(?array $data, string $class = null, array $exclude = []): ?AddressInterface
    {
        if (empty($data)) {
            return null;
        }

        $class = $class ?: Address::class;

        $address = new $class();

        foreach ($this->getProperties() as $property => $field) {
            if (in_array($property, $exclude, true)) {
                continue;
            }

            $field = $field ?? $property;

            if (!isset($data[$field])) {
                continue;
            }

            $value = $this->transformArrayProperty($property, $data[$field]);

            $this->accessor->setValue($address, $property, $value);
        }

        return $address;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function transformArrayProperty(string $property, $value)
    {
        if ('country' === $property) {
            $value = $this->countryRepository->findOneByCode($value);

            if (null === $value) {
                throw new InvalidArgumentException('Country not found.');
            }

            return $value;
        }

        /* TODO if ('state' === $property) {
            $value = $this->stateRepository->findOneByCode($value);

            if (null === $value) {
                throw new InvalidArgumentException('State not found.');
            }

            return $value;
        }*/

        if ('phone' === $property || 'mobile' === $property) {
            $value = $this->phoneNumberUtil->parse($value);

            if (!$value instanceof PhoneNumber) {
                throw new InvalidArgumentException('Invalid phone number.');
            }

            return $value;
        }

        return $value;
    }

    /**
     * Normalizes address into array.
     */
    public function transformAddress(?AddressInterface $address, array $exclude = []): ?array
    {
        if (null === $address) {
            return null;
        }

        $data = [];

        foreach ($this->getProperties() as $property => $field) {
            if (in_array($property, $exclude, true)) {
                continue;
            }

            $value = $this->accessor->getValue($address, $property);

            if (empty($value)) {
                continue;
            }

            $data[$field ?? $property] = $this->transformAddressProperty($property, $value);
        }

        if (empty($data)) {
            return null;
        }

        return $data;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function transformAddressProperty(string $property, $value)
    {
        if ('country' === $property) {
            if (!$value instanceof CountryInterface) {
                throw new UnexpectedTypeException($value, CountryInterface::class);
            }

            return $value->getCode();
        }

        if ('state' === $property) {
            if (!$value instanceof StateInterface) {
                throw new UnexpectedTypeException($value, StateInterface::class);
            }

            return $value->getCode();
        }

        if ('phone' === $property || 'mobile' === $property) {
            if (!$value instanceof PhoneNumber) {
                throw new UnexpectedTypeException($value, PhoneNumber::class);
            }

            return $this->phoneNumberUtil->format($value, PhoneNumberFormat::INTERNATIONAL);
        }

        return $value;
    }
}

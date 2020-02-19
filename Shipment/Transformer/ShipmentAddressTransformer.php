<?php

namespace Ekyna\Component\Commerce\Shipment\Transformer;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentAddress;
use libphonenumber\PhoneNumber;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class ShipmentAddressTransformer
 * @package Ekyna\Component\Commerce\Shipment\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAddressTransformer
{
    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * @var array
     */
    private $fields = [
        'company',
        'gender',
        'firstName',
        'lastName',
        'street',
        'supplement',
        'complement',
        'extra',
        'postalCode',
        'city',
        'country',
        'state',
        'phone',
        'mobile',
        'digicode1',
        'digicode2',
        'intercom',
        'latitude',
        'longitude',
        'information',
    ];

    /**
     * Constructor.
     *
     * @param CountryRepositoryInterface $countryRepository
     */
    public function __construct(CountryRepositoryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Returns the country repository.
     *
     * @return CountryRepositoryInterface
     */
    public function getCountryRepository()
    {
        return $this->countryRepository;
    }

    /**
     * Transforms an array into a shipment address.
     *
     * @param array $data
     *
     * @return ShipmentAddress
     */
    public function transform($data)
    {
        $address = new ShipmentAddress();

        if (!is_array($data) || empty($data)) {
            return $address;
        }

        foreach ($this->fields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];

                if ($field === 'country') {
                    if (0 >= $value) {
                        throw new InvalidArgumentException("Invalid country id.");
                    }
                    $value = $this->countryRepository->find($value);
                    if (null === $value) {
                        throw new InvalidArgumentException("Country not found.");
                    }
                } elseif ($field === 'phone' || $field === 'mobile') {
                    $value = unserialize($value);
                    if (!$value instanceof PhoneNumber) {
                        throw new InvalidArgumentException("Invalid phone number.");
                    }
                }

                $this->accessor->setValue($address, $field, $value);
            }
            // TODO Check required fields ?
        }

        return $address;
    }

    /**
     * Transforms a shipment address in to an array.
     *
     * @param ShipmentAddress $address
     *
     * @return array|null
     */
    public function reverseTransform($address)
    {
        if (null === $address) {
            return null;
        }

        if (!$address instanceof ShipmentAddress) {
            throw new InvalidArgumentException("Expected instance of " . ShipmentAddress::class);
        }

        $data = [];

        foreach ($this->fields as $field) {
            $value = $this->accessor->getValue($address, $field);
            if (empty($value)) {
                continue;
            }

            if ($value instanceof CountryInterface) {
                $value = $value->getId();
            } elseif ($value instanceof StateInterface) {
                $value = $value->getId();
            } elseif ($value instanceof PhoneNumber) {
                $value = serialize($value);
            }

            $data[$field] = $value;
        }

        if (empty($data)) {
            return null;
        }

        return $data;
    }
}

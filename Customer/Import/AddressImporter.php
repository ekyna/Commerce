<?php

namespace Ekyna\Component\Commerce\Customer\Import;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\EventListener\CustomerAddressListener;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerAddressRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AddressImporter
 * @package Ekyna\Component\Commerce\Customer\Import
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressImporter
{
    /**
     * @var CustomerAddressRepositoryInterface
     */
    private $addressRepository; // TODO use resource factory

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var PhoneNumberUtil
     */
    private $phoneNumberUtil;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var CustomerAddressListener
     */
    private $addressListener;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * @var AddressImport
     */
    private $config;

    /**
     * @var string[]
     */
    private $errors;

    /**
     * @var CustomerAddressInterface[]
     */
    private $addresses;

    /**
     * Constructor.
     *
     * @param CustomerAddressRepositoryInterface $addressRepository
     * @param CountryRepositoryInterface         $countryRepository
     * @param PhoneNumberUtil                    $phoneNumberUtil
     * @param ValidatorInterface                 $validator
     * @param EntityManagerInterface             $manager
     * @param CustomerAddressListener            $addressListener
     */
    public function __construct(
        CustomerAddressRepositoryInterface $addressRepository,
        CountryRepositoryInterface $countryRepository,
        PhoneNumberUtil $phoneNumberUtil,
        ValidatorInterface $validator,
        EntityManagerInterface $manager,
        CustomerAddressListener $addressListener
    ) {
        $this->addressRepository = $addressRepository;
        $this->countryRepository = $countryRepository;
        $this->phoneNumberUtil = $phoneNumberUtil;
        $this->validator = $validator;
        $this->manager = $manager;
        $this->addressListener = $addressListener;

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Imports the addresses from the given config.
     *
     * @param AddressImport $config
     *
     * @return int The imported addresses count.
     */
    public function import(AddressImport $config): int
    {
        $this->config = $config;
        $this->errors = [];
        $this->addresses = [];

        if (false === $handle = fopen($path = $config->getPath(), 'r')) {
            throw new RuntimeException("Failed to open file $path.");
        }

        $from = $config->getFrom() ?? 0;
        $to = $config->getTo() ?? INF;

        $line = 0;
        while ($data = fgetcsv($handle, 2048, $config->getSeparator(), $config->getEnclosure())) {
            $line++;

            if ($line < $from) {
                continue;
            }

            if ($line > $to) {
                continue;
            }

            $this->createAddress($config, $data, $line);
        }

        if (!empty($this->errors)) {
            $this->addresses = [];

            return 0;
        }

        return count($this->addresses);
    }

    /**
     * Persists the imported addresses.
     */
    public function flush(): void
    {
        if (empty($this->addresses)) {
            return;
        }

        if (!$this->config->getCustomer()->getDefaultInvoiceAddress()) {
            $address = reset($this->addresses);
            $address->setInvoiceDefault(true);
        }

        if (!$this->config->getCustomer()->getDefaultDeliveryAddress()) {
            $address = reset($this->addresses);
            $address->setDeliveryDefault(true);
        }

        $this->addressListener->setEnabled(false);

        $count = 0;
        foreach ($this->addresses as $address) {
            $count++;

            $this->manager->persist($address);

            if (0 === $count % 20) {
                $this->manager->flush();
            }
        }

        if (0 !== $count % 20) {
            $this->manager->flush();
        }

        $this->addressListener->setEnabled(true);
    }

    /**
     * Returns the errors.
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Creates the address.
     *
     * @param AddressImport $config
     * @param array         $data
     * @param int           $line
     */
    private function createAddress(AddressImport $config, array $data, int $line): void
    {
        /** @var CustomerAddressInterface $address */
        $address = $this->addressRepository->createNew();

        $address->setCustomer($config->getCustomer());

        $columns = $config->getColumns();

        foreach ($columns as $property => $column) {
            $column -= 1;
            if (!array_key_exists($column, $data)) {
                throw new InvalidArgumentException("No data at column $column of line $line.");
            }

            if (in_array($property, ['mobile', 'phone'], true)) {
                continue;
            }

            $datum = trim($data[$column]);

            if ($property === 'country') {
                if (!$datum = $this->countryRepository->findOneByCode($datum)) {
                    throw new InvalidArgumentException("Country $datum not found.");
                }
            }

            $this->accessor->setValue($address, $property, $datum);
        }

        if (!$address->getCountry()) {
            $address->setCountry($config->getDefaultCountry());
        }

        $country = $address->getCountry()->getCode();

        if (isset($columns['phone']) && !empty($number = trim($data[$columns['phone'] - 1]))) {
            $address->setPhone($this->phoneNumberUtil->parse($number, $country));
        }
        if (isset($columns['mobile']) && !empty($number = trim($data[$columns['mobile'] - 1]))) {
            $address->setMobile($this->phoneNumberUtil->parse($number, $country));
        }

        if (empty($address->getFirstName()) || empty($address->getLastName())) {
            $address->clearIdentity();
        }

        $violations = $this->validator->validate($address);

        if (0 < $violations->count()) {
            $this->errors[] = "Invalid address at line $line:\n " .
                implode(". \n", array_map(function (ConstraintViolationInterface $violation) {
                    if ($path = $violation->getPropertyPath()) {
                        return sprintf('[%s] (%s) %s', $path, $violation->getInvalidValue(), $violation->getMessage());
                    }

                    return sprintf('(%s) %s', $violation->getInvalidValue(), $violation->getMessage());
                }, iterator_to_array($violations)));

            return;
        }

        $this->addresses[] = $address;
    }
}

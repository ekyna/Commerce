<?php

namespace Ekyna\Component\Commerce\Customer\Import;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class AddressImportConfig
 * @package Ekyna\Component\Commerce\Customer\Import
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressImport
{
    /**
     * @var array
     */
    private static $columnKeys = [
        'company',
        'gender',
        'firstName',
        'lastName',
        'street',
        'complement',
        'supplement',
        'extra',
        'postalCode',
        'city',
        'country',
        // TODO 'state',
        'phone',
        'mobile',
        'digicode1',
        'digicode2',
        'intercom',
    ];

    /**
     * Returns the available column keys.
     *
     * @return array
     */
    public static function getColumnKeys(): array
    {
        return self::$columnKeys;
    }

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var int
     */
    private $from;

    /**
     * @var int
     */
    private $to;

    /**
     * @var string
     */
    private $separator = ';';

    /**
     * @var string
     */
    private $enclosure = '"';

    /**
     * @var CountryInterface
     */
    private $defaultCountry;


    /**
     * Constructor.
     *
     * @param CustomerInterface $customer
     */
    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;

        $this->columns = array_fill_keys([
            'company',
            'street',
            'postalCode',
            'city',
            'phone',
            'mobile',
        ], null);
    }

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    /**
     * Returns the file path.
     *
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Sets the file path.
     *
     * @param string $path
     *
     * @return AddressImport
     */
    public function setPath(string $path): AddressImport
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns the columns.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Sets the columns.
     *
     * @param array $columns
     *
     * @return AddressImport
     */
    public function setColumns(array $columns): AddressImport
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Returns the from.
     *
     * @return int
     */
    public function getFrom(): ?int
    {
        return $this->from;
    }

    /**
     * Sets the from.
     *
     * @param int $from
     *
     * @return AddressImport
     */
    public function setFrom(int $from = null): AddressImport
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Returns the to.
     *
     * @return int
     */
    public function getTo(): ?int
    {
        return $this->to;
    }

    /**
     * Sets the to.
     *
     * @param int $to
     *
     * @return AddressImport
     */
    public function setTo(int $to = null): AddressImport
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Returns the separator.
     *
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * Sets the separator.
     *
     * @param string $separator
     *
     * @return AddressImport
     */
    public function setSeparator(string $separator): AddressImport
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Returns the enclosure.
     *
     * @return string
     */
    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    /**
     * Sets the enclosure.
     *
     * @param string $enclosure
     *
     * @return AddressImport
     */
    public function setEnclosure(string $enclosure): AddressImport
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Returns the default country.
     *
     * @return CountryInterface
     */
    public function getDefaultCountry(): ?CountryInterface
    {
        return $this->defaultCountry;
    }

    /**
     * Sets the default country.
     *
     * @param CountryInterface $country
     *
     * @return AddressImport
     */
    public function setDefaultCountry(CountryInterface $country = null): AddressImport
    {
        $this->defaultCountry = $country;

        return $this;
    }
}

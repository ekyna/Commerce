<?php

namespace Ekyna\Component\Commerce\Customer\Validator;

/**
 * Class VatResult
 * @package Ekyna\Component\Commerce\Customer\Validator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatResult
{
    /**
     * @var bool
     */
    private $valid;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $date;


    /**
     * Constructor.
     *
     * @param bool   $valid
     * @param string $country
     * @param string $number
     * @param string $name
     * @param string $address
     */
    public function __construct($valid = false, $country = '', $number = '', $name = '', $address = '')
    {
        $this->valid = $valid;
        $this->country = $country;
        $this->number = $number;
        $this->name = $name;
        $this->address = $address;
        $this->date = date('c');
    }

    /**
     * Returns the valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Returns the details.
     *
     * @return array
     */
    public function getDetails()
    {
        return [
            'valid' => $this->valid,
            'country' => $this->country,
            'number' => $this->number,
            'name' => $this->name,
            'address' => $this->address,
            'date' => $this->date,
        ];
    }
}

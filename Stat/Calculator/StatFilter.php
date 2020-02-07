<?php

namespace Ekyna\Component\Commerce\Stat\Calculator;

/**
 * Class StatFilter
 * @package Ekyna\Component\Commerce\Stat\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatFilter
{
    /**
     * @var string[]
     */
    private $countries = [];

    /**
     * @var bool
     */
    private $excludeCountries = false;


    /**
     * Returns the countries codes.
     *
     * @return string[]
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * Sets the countries codes.
     *
     * @param string[] $codes
     *
     * @return StatFilter
     */
    public function setCountries(array $codes): self
    {
        $this->countries = [];

        foreach ($codes as $code) {
            $this->addCountry($code);
        }

        return $this;
    }

    /**
     * Adds the country code.
     *
     * @param string $code
     *
     * @return StatFilter
     */
    public function addCountry(string $code): self
    {
        $this->countries[] = strtoupper($code);

        return $this;
    }

    /**
     * Returns whether to exclude countries.
     *
     * @return bool
     */
    public function isExcludeCountries(): bool
    {
        return $this->excludeCountries;
    }

    /**
     * Sets whether to exclude countries.
     *
     * @param bool $exclude
     *
     * @return StatFilter
     */
    public function setExcludeCountries(bool $exclude): self
    {
        $this->excludeCountries = $exclude;

        return $this;
    }

    /**
     * Returns whether the filter is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->countries);
    }
}

<?php

namespace Ekyna\Component\Commerce\Common\Country;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;

/**
 * Interface CountryProviderInterface
 * @package Ekyna\Component\Commerce\Common\Country
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CountryProviderInterface
{
    /**
     * Returns the available countries.
     *
     * @return array
     */
    public function getAvailableCountries(): array;

    /**
     * Returns the fallback country.
     *
     * @return string
     */
    public function getFallbackCountry(): string;

    /**
     * Returns the current country.
     *
     * @return string
     */
    public function getCurrentCountry(): string;

    /**
     * Sets the current country.
     *
     * @param string|CountryInterface $country
     *
     * @return CountryProviderInterface
     */
    public function setCountry($country): self;

    /**
     * Returns the country (entity) by its code, or the current one if no code is provided.
     *
     * @param string $code
     *
     * @return CountryInterface
     */
    public function getCountry(string $code = null): CountryInterface;
}

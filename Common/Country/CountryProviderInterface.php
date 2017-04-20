<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Country;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;

/**
 * Interface CountryProviderInterface
 * @package Ekyna\Component\Commerce\Common\Country
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CountryProviderInterface
{
    /**
     * Returns the available countries.
     */
    public function getAvailableCountries(): array;

    /**
     * Returns the fallback country.
     */
    public function getFallbackCountry(): string;

    /**
     * Returns the current country.
     */
    public function getCurrentCountry(): string;

    /**
     * Sets the current country.
     *
     * @param string|CountryInterface $country
     */
    public function setCountry($country): self;

    /**
     * Returns the country (entity) by its code, or the current one if no code is provided.
     */
    public function getCountry(string $code = null): CountryInterface;

    /**
     * Returns the default country.
     */
    public function getDefault(): CountryInterface;

    /**
     * Returns the country repository.
     */
    public function getCountryRepository(): CountryRepositoryInterface;
}

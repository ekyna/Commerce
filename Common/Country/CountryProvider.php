<?php

namespace Ekyna\Component\Commerce\Common\Country;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

/**
 * Class CountryProvider
 * @package Ekyna\Component\Commerce\Common\Country
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryProvider implements CountryProviderInterface
{
    /**
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

    /**
     * @var string
     */
    protected $fallbackCountry;

    /**
     * @var string
     */
    protected $currentCountry;


    /**
     * Constructor.
     *
     * @param CountryRepositoryInterface $countryRepository
     * @param string                      $fallbackCountry
     * @param string                      $currentCountry
     */
    public function __construct(
        CountryRepositoryInterface $countryRepository,
        string $fallbackCountry,
        string $currentCountry = null
    ) {
        $this->countryRepository = $countryRepository;
        $this->fallbackCountry = $fallbackCountry;
        $this->currentCountry = $currentCountry;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableCountries(): array
    {
        return $this->countryRepository->findEnabledCodes();
    }

    /**
     * @inheritDoc
     */
    public function getFallbackCountry(): string
    {
        return $this->fallbackCountry;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCountry(): string
    {
        if (!empty($this->currentCountry)) {
            return $this->currentCountry;
        }

        if (!empty($country = $this->guessCountry())) {
            return $this->currentCountry = $country;
        }

        return $this->currentCountry = $this->getFallbackCountry();
    }

    /**
     * @inheritDoc
     */
    public function setCountry($country): CountryProviderInterface
    {
        $country = $country instanceof CountryInterface ? $country->getCode() : $country;

        if (!is_string($country)) {
            throw new UnexpectedValueException("Expected string or instance of " . CountryInterface::class);
        }

        if (!in_array($country, $this->getAvailableCountries(), true)) {
            throw new UnexpectedValueException("Country $country is not available.");
        }

        $this->currentCountry = $country;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCountry(string $code = null): CountryInterface
    {
        return $this->countryRepository->findOneByCode($code ?? $this->getCurrentCountry());
    }

    /**
     * @inheritDoc
     */
    public function getDefault(): CountryInterface
    {
        return $this->countryRepository->findDefault();
    }

    /**
     * @inheritDoc
     */
    public function getCountryRepository(): CountryRepositoryInterface
    {
        return $this->countryRepository;
    }

    /**
     * Guesses the user country.
     *
     * @return string|null
     */
    protected function guessCountry(): ?string
    {
        return null;
    }
}

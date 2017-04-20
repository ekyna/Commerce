<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Country;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

/**
 * Class CountryProvider
 * @package Ekyna\Component\Commerce\Common\Country
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryProvider implements CountryProviderInterface
{
    protected CountryRepositoryInterface $countryRepository;
    protected string $fallbackCountry;
    protected ?string $currentCountry;


    public function __construct(
        CountryRepositoryInterface $countryRepository,
        string $fallbackCountry,
        string $currentCountry = null
    ) {
        $this->countryRepository = $countryRepository;
        $this->fallbackCountry = $fallbackCountry;
        $this->currentCountry = $currentCountry;
    }

    public function getAvailableCountries(): array
    {
        return $this->countryRepository->findEnabledCodes();
    }

    public function getFallbackCountry(): string
    {
        return $this->fallbackCountry;
    }

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
            throw new UnexpectedTypeException($country, ['string', CountryInterface::class]);
        }

        if (!in_array($country, $this->getAvailableCountries(), true)) {
            throw new UnexpectedValueException("Country $country is not available.");
        }

        $this->currentCountry = $country;

        return $this;
    }

    public function getCountry(string $code = null): CountryInterface
    {
        return $this->countryRepository->findOneByCode($code ?? $this->getCurrentCountry());
    }

    public function getDefault(): CountryInterface
    {
        return $this->countryRepository->findDefault();
    }

    public function getCountryRepository(): CountryRepositoryInterface
    {
        return $this->countryRepository;
    }

    /**
     * Guesses the user country.
     */
    protected function guessCountry(): ?string
    {
        return null;
    }
}

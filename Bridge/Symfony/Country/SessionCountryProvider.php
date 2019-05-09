<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Country;

use Ekyna\Component\Commerce\Common\Country\CountryProvider;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionCountryProvider
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Country
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCountryProvider extends CountryProvider
{
    private const KEY = 'ekyna_commerce/country';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $key;


    /**
     * Constructor.
     *
     * @param CountryRepositoryInterface $countryRepository
     * @param SessionInterface           $session
     * @param string                     $fallbackCountry
     * @param string                     $key
     */
    public function __construct(
        CountryRepositoryInterface $countryRepository,
        SessionInterface $session,
        string $fallbackCountry,
        string $key = self::KEY
    ) {
        parent::__construct($countryRepository, $fallbackCountry);

        $this->session = $session;
        $this->key = $key;
    }

    /**
     * @inheritDoc
     */
    public function setCountry($country): CountryProviderInterface
    {
        parent::setCountry($country);

        $this->save();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCountry(): string
    {
        if ($this->currentCountry) {
            return $this->currentCountry;
        }

        if ($this->session->has($this->key)) {
            if (null !== $country = $this->session->get($this->key)) {
                parent::setCountry($country);

                return $this->currentCountry;
            }
        }

        parent::getCurrentCountry();

        $this->save();

        return $this->currentCountry;
    }

    /**
     * Saves the current country into the session.
     */
    private function save()
    {
        $this->session->set($this->key, $this->currentCountry);
    }
}

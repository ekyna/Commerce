<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Country;

use Ekyna\Component\Commerce\Common\Country\CountryProvider;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SessionCountryProvider
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Country
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCountryProvider extends CountryProvider
{
    private const KEY = 'ekyna_commerce/country';

    private RequestStack $requestStack;
    private string       $key;


    public function __construct(
        CountryRepositoryInterface $countryRepository,
        RequestStack $requestStack,
        string $fallbackCountry,
        string $key = self::KEY
    ) {
        parent::__construct($countryRepository, $fallbackCountry);

        $this->requestStack = $requestStack;
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

    public function getCurrentCountry(): string
    {
        if (!empty($this->currentCountry)) {
            return $this->currentCountry;
        }

        try {
            $session = $this->requestStack->getSession();

            if ($session->has($this->key) && !empty($country = $session->get($this->key))) {
                parent::setCountry($country);

                return $this->currentCountry;
            }
        } catch (SessionNotFoundException) {
        }

        parent::getCurrentCountry();

        $this->save();

        return $this->currentCountry;
    }

    /**
     * Saves the current country into the session.
     */
    private function save(): void
    {
        try {
            $this
                ->requestStack
                ->getSession()
                ->set($this->key, $this->currentCountry);
        } catch (SessionNotFoundException) {
        }
    }
}

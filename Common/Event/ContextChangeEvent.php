<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ContextChangeEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ContextChangeEvent extends Event
{
    private ?CurrencyInterface $currency;
    private ?CountryInterface $country;
    private ?string $locale;


    public function __construct(
        CurrencyInterface $currency = null,
        CountryInterface $country = null,
        string $locale = null
    ) {
        $this->currency = $currency;
        $this->country = $country;
        $this->locale = $locale;
    }

    /**
     * Returns the currency.
     *
     * @return CurrencyInterface|null
     */
    public function getCurrency(): ?CurrencyInterface
    {
        return $this->currency;
    }

    /**
     * Returns the country.
     *
     * @return CountryInterface|null
     */
    public function getCountry(): ?CountryInterface
    {
        return $this->country;
    }

    /**
     * Returns the locale.
     *
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }
}

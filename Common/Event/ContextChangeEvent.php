<?php

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ContextChangeEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ContextChangeEvent extends Event
{
    /**
     * @var CurrencyInterface
     */
    private $currency;

    /**
     * @var CountryInterface
     */
    private $country;


    /**
     * Constructor.
     *
     * @param CurrencyInterface $currency
     * @param CountryInterface  $country
     */
    public function __construct(CurrencyInterface $currency = null, CountryInterface $country = null)
    {
        $this->currency = $currency;
        $this->country = $country;
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
}

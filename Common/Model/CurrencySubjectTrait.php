<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait CurrencySubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait CurrencySubjectTrait
{
    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @var float
     */
    protected $exchangeRate;


    /**
     * Returns the currency.
     *
     * @return CurrencyInterface|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|CurrencySubjectInterface
     */
    public function setCurrency(CurrencyInterface $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Returns the exchange rate.
     *
     * @return float|null
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * Sets the exchange rate.
     *
     * @param float $rate
     *
     * @return $this|CurrencySubjectInterface
     */
    public function setExchangeRate($rate)
    {
        $this->exchangeRate = $rate;

        return $this;
    }
}

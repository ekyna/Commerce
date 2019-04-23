<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait ExchangeSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ExchangeSubjectTrait
{
    use CurrencySubjectTrait;

    /**
     * @var float
     */
    protected $exchangeRate;

    /**
     * @var \DateTime
     */
    protected $exchangeDate;


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
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeRate($rate)
    {
        $this->exchangeRate = $rate;

        return $this;
    }

    /**
     * Returns the exchange date.
     *
     * @return \DateTime
     */
    public function getExchangeDate()
    {
        return $this->exchangeDate;
    }

    /**
     * Sets the exchange date.
     *
     * @param \DateTime $exchangeDate
     *
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeDate(\DateTime $exchangeDate = null)
    {
        $this->exchangeDate = $exchangeDate;

        return $this;
    }
}

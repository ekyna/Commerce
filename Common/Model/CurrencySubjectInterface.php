<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface CurrencySubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencySubjectInterface
{
    /**
     * Returns the currency.
     *
     * @return CurrencyInterface|null
     */
    public function getCurrency();

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|CurrencySubjectInterface
     */
    public function setCurrency(CurrencyInterface $currency);

    /**
     * Returns the exchange rate.
     *
     * @return float|null
     */
    public function getExchangeRate();

    /**
     * Sets the exchange rate.
     *
     * @param float $rate
     *
     * @return $this|CurrencySubjectInterface
     */
    public function setExchangeRate($rate);
}

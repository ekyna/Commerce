<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface ExchangeSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ExchangeSubjectInterface extends CurrencySubjectInterface
{
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
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeRate($rate);

    /**
     * Returns the exchange date.
     *
     * @return \DateTime
     */
    public function getExchangeDate();

    /**
     * Sets the exchange date.
     *
     * @param \DateTime $date
     *
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeDate(\DateTime $date = null);
}

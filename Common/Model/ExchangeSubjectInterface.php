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
     * Returns the exchange rate (DEFAULT/SUBJECT currencies pair).
     *
     * @return float|null
     */
    public function getExchangeRate(): ?float;

    /**
     * Sets the exchange rate (DEFAULT/SUBJECT currencies pair).
     *
     * @param float $rate
     *
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeRate(float $rate = null): ExchangeSubjectInterface;

    /**
     * Returns the exchange date.
     *
     * @return \DateTime|null
     */
    public function getExchangeDate(): ?\DateTime;

    /**
     * Sets the exchange date.
     *
     * @param \DateTime $date
     *
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeDate(\DateTime $date = null): ExchangeSubjectInterface;
}

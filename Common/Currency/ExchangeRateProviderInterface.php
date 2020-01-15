<?php

namespace Ekyna\Component\Commerce\Common\Currency;

/**
 * Interface ExchangeRateProviderInterface
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ExchangeRateProviderInterface
{
    /**
     * Returns the exchange rate.
     *
     * @param string    $base
     * @param string    $quote
     * @param \DateTime $date
     *
     * @return float|null
     */
    public function get(string $base, string $quote, \DateTime $date): ?float;
}

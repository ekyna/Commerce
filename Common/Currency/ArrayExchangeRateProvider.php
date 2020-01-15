<?php

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ArrayExchangeRateProvider
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ArrayExchangeRateProvider extends AbstractExchangeRateProvider
{
    /**
     * @var array
     */
    private $rates;


    /**
     * Constructor.
     *
     * @param array                         $rates
     * @param ExchangeRateProviderInterface $fallback
     */
    public function __construct(array $rates, ExchangeRateProviderInterface $fallback = null)
    {
        parent::__construct($fallback);

        $this->rates = $rates;
    }

    /**
     * @inheritDoc
     */
    protected function fetch(string $base, string $quote, \DateTime $date): ?float
    {
        if (isset($this->rates["$base/$quote"])) {
            return $this->rates["$base/$quote"];
        }

        if (isset($this->rates["$quote/$base"])) {
            return 1 / $this->rates["$quote/$base"];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function persist(string $base, string $quote, \DateTime $date, float $rate): void
    {
        if (!(is_float($rate) && 0 < $rate)) {
            throw new InvalidArgumentException("Unexpected rate '$rate'.");
        }

        $base = strtoupper($base);
        $quote = strtoupper($quote);

        $pair = "$quote/$base";

        if (!preg_match('~^[A-Z]{3}/[A-Z]{3}$~', $pair)) {
            throw new InvalidArgumentException("Unexpected currency pair '$pair'.");
        }

        if (isset($this->rates["$quote/$base"])) {
            $this->rates["$quote/$base"] = $rate;

            return;
        }

        $this->rates["$base/$quote"] = $rate;
    }
}

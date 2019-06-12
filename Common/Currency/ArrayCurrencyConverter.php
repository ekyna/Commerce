<?php

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ArrayCurrencyConverter
 * @package Ekyna\Component\Commerce\Common\Converter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ArrayCurrencyConverter extends AbstractCurrencyConverter
{
    /**
     * @var array
     */
    private $rates;


    /**
     * Constructor.
     *
     * @param array  $rates
     * @param string $defaultCurrency
     */
    public function __construct(array $rates, string $defaultCurrency = 'USD')
    {
        parent::__construct($defaultCurrency);

        $this->rates = [];
        foreach ($rates as $pair => $rate) {
            $this->addRate($pair, $rate);
        }
    }

    /**
     * Adds the conversion rate.
     *
     * @param string $pair
     * @param float  $rate
     *
     * @return ArrayCurrencyConverter
     */
    private function addRate($pair, $rate): self
    {
        if (!preg_match('~^[A-Z]{3}/[A-Z]{3}$~', $pair)) {
            throw new InvalidArgumentException("Unexpected currency pair '$pair'.");
        }

        if (!(is_float($rate) && 0 < $rate)) {
            throw new InvalidArgumentException("Unexpected rate '$rate'.");
        }

        $this->rates[$pair] = $rate;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRate($base, $quote = null, \DateTime $date = null)
    {
        $base = strtoupper($base);
        $quote = strtoupper($quote ? $quote : $this->defaultCurrency);

        if ($base === $quote) {
            return 1.0;
        }

        if (isset($this->rates["$base/$quote"])) {
            return $this->rates["$base/$quote"];
        }

        if (isset($this->rates["$quote/$base"])) {
            return 1 / $this->rates["$quote/$base"];
        }

        throw new InvalidArgumentException("Neither '$base/$quote' or '$quote/$base' conversion pair are defined.");
    }
}

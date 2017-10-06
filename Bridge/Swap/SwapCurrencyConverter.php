<?php

namespace Ekyna\Component\Commerce\Bridge\Swap;

use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Swap\Swap;

/**
 * Class SwapCurrencyConverter
 * @package Ekyna\Component\Commerce\Bridge\Swap
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SwapCurrencyConverter implements CurrencyConverterInterface
{
    /**
     * @var Swap
     */
    private $swap;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param Swap   $swap
     * @param string $defaultCurrency
     */
    public function __construct(Swap $swap, $defaultCurrency = 'USD')
    {
        $this->swap = $swap;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritDoc
     */
    public function convert($amount, $base, $quote = null, \DateTime $date = null)
    {
        $quote = $quote ?: $this->defaultCurrency;

        $pair = "$base/$quote";
        if (null !== $date && $date <= new \DateTime()) {
            $rate = $this->swap->historical($pair, $date)->getValue();
        } else {
            $rate = $this->swap->latest($pair)->getValue();
        }

        return Money::round($amount / $rate, $quote);
    }
}

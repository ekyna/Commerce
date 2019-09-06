<?php

namespace Ekyna\Component\Commerce\Bridge\Swap;

use Ekyna\Component\Commerce\Common\Currency\AbstractCurrencyConverter;
use Swap\Swap;

/**
 * Class CurrencyConverter
 * @package Ekyna\Component\Commerce\Bridge\Swap
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyConverter extends AbstractCurrencyConverter
{
    /**
     * @var Swap
     */
    private $swap;


    /**
     * Constructor.
     *
     * @param Swap   $swap
     * @param string $defaultCurrency
     */
    public function __construct(Swap $swap, string $defaultCurrency = 'USD')
    {
        parent::__construct($defaultCurrency);

        $this->swap = $swap;
    }

    /**
     * @inheritdoc
     */
    public function getRate(string $base, string $quote = null, \DateTime $date = null): float
    {
        $base = strtoupper($base);
        $quote = strtoupper($quote ?? $this->defaultCurrency);

        if ($base === $quote) {
            return 1.0;
        }

        if ($quote === $this->defaultCurrency) {
            $invert = true;
            $pair = "$quote/$base";
        } else {
            $invert = false;
            $pair = "$base/$quote";
        }

        if (null !== $date && 1 <= $date->diff(new \DateTime())->h) {
            $rate = $this->swap->historical($pair, $date)->getValue();
        } else {
            $rate = $this->swap->latest($pair)->getValue();
        }

        return (float) $invert ?  1 / $rate : $rate;
    }
}

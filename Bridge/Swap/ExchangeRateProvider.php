<?php

namespace Ekyna\Component\Commerce\Bridge\Swap;

use Ekyna\Component\Commerce\Common\Currency\AbstractExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Exchanger\Exception\Exception;
use Swap\Swap;

/**
 * Class ExchangeRateProvider
 * @package Ekyna\Component\Commerce\Bridge\Swap
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExchangeRateProvider extends AbstractExchangeRateProvider
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
     * @param Swap                               $swap
     * @param string                             $defaultCurrency
     * @param ExchangeRateProviderInterface|null $fallback
     */
    public function __construct(Swap $swap, string $defaultCurrency, ExchangeRateProviderInterface $fallback = null)
    {
        parent::__construct($fallback);

        $this->swap            = $swap;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritDoc
     */
    protected function fetch(string $base, string $quote, \DateTime $date): ?float
    {
        if (null !== $rate = $this->find($base, $quote, $date)) {
            return $rate;
        }

        if (null !== $rate = $this->find($quote, $base, $date)) {
            return 1 / $rate;
        }

        return null;
    }

    /**
     * Finds the exchange rate.
     *
     * @param string    $base
     * @param string    $quote
     * @param \DateTime $date
     *
     * @return float|null
     */
    protected function find(string $base, string $quote, \DateTime $date): ?float
    {
        if (!is_null($date) && (1 <= $date->diff(new \DateTime())->h)) {
            try {
                return (float)$this->swap->historical("$base/$quote", $date)->getValue();
            } catch (Exception $e) {
            }

            return null;
        }

        try {
            return (float)$this->swap->latest("$base/$quote")->getValue();
        } catch (Exception $e) {
        }

        return null;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    protected function persist(string $base, string $quote, \DateTime $date, float $rate): void
    {
        return;
    }
}

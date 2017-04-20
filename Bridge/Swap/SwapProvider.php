<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Swap;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\AbstractExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Exchanger\Exception\Exception;
use Swap\Swap;

/**
 * Class SwapProvider
 * @package Ekyna\Component\Commerce\Bridge\Swap
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SwapProvider extends AbstractExchangeRateProvider
{
    private Swap $swap;


    public function __construct(Swap $swap, ExchangeRateProviderInterface $fallback = null)
    {
        parent::__construct($fallback);

        $this->swap = $swap;
    }

    protected function fetch(string $base, string $quote, DateTimeInterface $date): ?Decimal
    {
        if ($rate = $this->find($base, $quote, $date)) {
            return $rate;
        }

        if ($rate = $this->find($quote, $base, $date)) {
            return (new Decimal(1))->div($rate);
        }

        return null;
    }

    /**
     * Finds the exchange rate.
     */
    protected function find(string $base, string $quote, DateTimeInterface $date): ?Decimal
    {
        $diff = $date->diff(new DateTime());
        if (0 < $diff->days || 0 < $diff->h) {
            try {
                $rate = $this->swap->historical("$base/$quote", $date)->getValue();

                return new Decimal((string)$rate);
            } catch (Exception $e) {
            }

            return null;
        }

        try {
            $rate = $this->swap->latest("$base/$quote")->getValue();

            return new Decimal((string)$rate);
        } catch (Exception $e) {
        }

        return null;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    protected function persist(string $base, string $quote, DateTimeInterface $date, Decimal $rate): void
    {
    }
}

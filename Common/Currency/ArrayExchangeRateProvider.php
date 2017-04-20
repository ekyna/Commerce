<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ArrayExchangeRateProvider
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ArrayExchangeRateProvider extends AbstractExchangeRateProvider
{
    /** @var array<string, Decimal> */
    private array $rates;

    public function __construct(array $rates, ExchangeRateProviderInterface $fallback = null)
    {
        parent::__construct($fallback);

        $this->rates = $rates;
    }

    protected function fetch(string $base, string $quote, DateTimeInterface $date): ?Decimal
    {
        if (isset($this->rates["$base/$quote"])) {
            return $this->rates["$base/$quote"];
        }

        if (isset($this->rates["$quote/$base"])) {
            return (new Decimal(1))->div($this->rates["$quote/$base"]);
        }

        return null;
    }

    protected function persist(string $base, string $quote, DateTimeInterface $date, Decimal $rate): void
    {
        if ($rate->isZero()) {
            throw new InvalidArgumentException('Unexpected rate.');
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

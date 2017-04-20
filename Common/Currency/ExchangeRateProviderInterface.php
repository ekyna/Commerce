<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use DateTimeInterface;
use Decimal\Decimal;

/**
 * Interface ExchangeRateProviderInterface
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ExchangeRateProviderInterface
{
    /**
     * Returns the exchange rate.
     */
    public function get(string $base, string $quote, DateTimeInterface $date): ?Decimal;
}

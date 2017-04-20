<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use Decimal\Decimal;

/**
 * Class CurrencyRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyRendererInterface
{
    /**
     * Configures the currency renderer.
     *
     * @param mixed        $quote  If null, other arguments are ignored.
     * @param Decimal|null $rate   The exchange rate (to override).
     * @param string|null  $locale The locale.
     */
    public function configure($quote = null, Decimal $rate = null, string $locale = null): void;

    /**
     * Returns the base (default) currency.
     */
    public function getBase(): string;

    /**
     * Returns the configured quote currency.
     */
    public function getQuote(): string;

    /**
     * Renders the exchange rate.
     */
    public function renderRate($quote = null, bool $invert = true, bool $withDate = false): string;

    /**
     * Renders the amount in quote currency and optionally in base currency.
     *
     * @param Decimal $amount   The amount in default currency.
     * @param mixed   $quote    The quote subject or currency.
     * @param bool    $withBase Whether to render in base currency.
     */
    public function renderQuote(Decimal $amount, $quote = null, bool $withBase = false): string;

    /**
     * Renders the amount in base currency and optionally in quote currency.
     *
     * @param Decimal $amount    The amount in default currency.
     * @param mixed   $quote     The quote subject or currency.
     * @param bool    $withQuote Whether to render in quote currency.
     */
    public function renderBase(Decimal $amount, $quote = null, bool $withQuote = false);
}

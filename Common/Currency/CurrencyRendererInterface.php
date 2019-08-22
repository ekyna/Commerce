<?php

namespace Ekyna\Component\Commerce\Common\Currency;


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
     * @param mixed       $quote  If null, other arguments are ignored.
     * @param float|null  $rate   The exchange rate (to override).
     * @param string|null $locale The locale.
     */
    public function configure($quote = null, float $rate = null, string $locale = null): void;

    /**
     * Returns the base (default) currency.
     *
     * @return string
     */
    public function getBase(): string;

    /**
     * Returns the configured quote currency.
     *
     * @return string
     */
    public function getQuote(): string;

    /**
     * Renders the exchange rate.
     *
     * @param mixed $quote
     * @param bool  $invert
     * @param bool  $withDate
     *
     * @return string
     */
    public function renderRate($quote = null, bool $invert = true, bool $withDate = false): string;

    /**
     * Renders the amount in quote currency and optionally in base currency.
     *
     * @param float $amount   The amount in default currency.
     * @param mixed $quote    The quote subject or currency.
     * @param bool  $withBase Whether to render in base currency.
     *
     * @return string
     */
    public function renderQuote(float $amount, $quote = null, bool $withBase = false): string;

    /**
     * Renders the amount in base currency and optionally in quote currency.
     *
     * @param float $amount    The amount in default currency.
     * @param mixed $quote     The quote subject or currency.
     * @param bool  $withQuote Whether to render in quote currency.
     *
     * @return string
     */
    public function renderBase(float $amount, $quote = null, bool $withQuote = false);
}
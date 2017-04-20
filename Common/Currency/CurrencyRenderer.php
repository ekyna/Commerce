<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencySubjectInterface;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

/**
 * Class CurrencyRenderer
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CurrencyRenderer implements CurrencyRendererInterface
{
    private CurrencyConverterInterface $currencyConverter;
    private FormatterFactory           $formatterFactory;

    private string             $base;
    private ?string            $quote  = null;
    private ?Decimal             $rate   = null;
    private ?DateTimeInterface $date   = null;
    private ?string            $locale = null;


    public function __construct(CurrencyConverterInterface $currencyConverter, FormatterFactory $formatterFactory)
    {
        $this->currencyConverter = $currencyConverter;
        $this->formatterFactory = $formatterFactory;

        $this->base = $this->currencyConverter->getDefaultCurrency();
    }

    /**
     * @inheritDoc
     */
    public function configure($quote = null, Decimal $rate = null, string $locale = null): void
    {
        if (is_null($quote)) {
            $this->quote = null;
            $this->rate = null;
            $this->date = null;
            $this->locale = null;

            return;
        }

        [$this->quote, $this->rate, $this->date] = $this->resolve($quote);

        if (!is_null($rate)) {
            $this->rate = $rate;
        }

        $this->locale = $locale;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function getQuote(): string
    {
        if (!$this->quote) {
            throw new RuntimeException('Currency renderer is not configured');
        }

        return $this->quote;
    }

    /**
     * @inheritDoc
     */
    public function renderRate($quote = null, bool $invert = true, bool $withDate = false): string
    {
        if ($quote) {
            [$quote, $rate, $date] = $this->resolve($quote);
        } elseif ($this->isConfigured()) {
            $quote = $this->quote;
            $rate = $this->rate;
            $date = $this->date;
        } else {
            throw new RuntimeException('You must either provide a value as quote argument or call configure().');
        }

        if ($quote === $this->base) {
            return '';
        }

        $formatter = $this->formatterFactory->create($this->locale);

        if ($invert) {
            $pair = "$quote/$this->base";
            $rate = $formatter->number(1 / $rate, 5);
        } else {
            $pair = "$this->base/$quote";
            $rate = $formatter->number($rate, 5);
        }

        if ($withDate) {
            return sprintf('%s&nbsp;%s&nbsp;(%s)', $pair, $rate, $formatter->date($date));
        }

        return sprintf('%s&nbsp;%s', $pair, $rate);
    }

    /**
     * @inheritDoc
     */
    public function renderQuote(Decimal $amount, $quote = null, bool $withBase = false): string
    {
        if ($quote) {
            [$quote, $rate] = $this->resolve($quote);
        } elseif ($this->isConfigured()) {
            $quote = $this->quote;
            $rate = $this->rate;
        } else {
            throw new RuntimeException('You must either provide a value as quote argument or call configure().');
        }

        $output = $this->format($amount, $quote, $rate);

        if ($withBase && (0 != $amount) && ($quote !== $this->base)) {
            $output .= ' <em class="text-muted">(' . $this->format($amount, $this->base) . ')</em>';
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function renderBase(Decimal $amount, $quote = null, bool $withQuote = false): string
    {
        if ($quote) {
            [$quote, $rate] = $this->resolve($quote);
        } elseif ($this->isConfigured()) {
            $quote = $this->quote;
            $rate = $this->rate;
        } else {
            throw new RuntimeException('You must either provide a value as quote argument or call configure().');
        }

        $output = $this->format($amount, $this->base);

        if ($withQuote && (0 != $amount) && ($quote !== $this->base)) {
            $output .= ' <em class="text-muted">(' . $this->format($amount, $quote, $rate) . ')</em>';
        }

        return $output;
    }

    /**
     * Resolves the currency and exchange rate from the given quote (subject or currency).
     *
     * @param mixed $quote
     *
     * @return array [(string) currency, (Decimal) rate, (\DateTime) $date]
     */
    protected function resolve($quote): array
    {
        if ($quote instanceof ExchangeSubjectInterface) {
            if (is_null($currency = $quote->getCurrency())) {
                throw new RuntimeException('Exchange subject\'s currency is not defined.');
            }

            $code = $currency->getCode();

            $rate = $this->currencyConverter->getSubjectExchangeRate($quote, $this->base, $code);

            $date = $quote->getExchangeDate() ?? new DateTime();

            return [$code, $rate, $date];
        }

        $date = new DateTime();

        if ($quote instanceof CurrencySubjectInterface) {
            $quote = $quote->getCurrency();
        }

        if ($quote instanceof CurrencyInterface) {
            $quote = $quote->getCode();
        }

        if (is_string($quote) && !empty($quote)) {
            $rate = $this->currencyConverter->getRate($this->base, $quote, $date);

            return [$quote, $rate, $date];
        }

        throw new UnexpectedValueException(sprintf(
            'Expected string or instance of %s, %s or %s',
            ExchangeSubjectInterface::class,
            CurrencySubjectInterface::class,
            CurrencyInterface::class
        ));
    }

    /**
     * Formats the given amount. Amount is converted if currency is not the default one.
     */
    protected function format(Decimal $amount, string $currency, Decimal $rate = null): string
    {
        if (!is_null($rate)) {
            $amount = $this->currencyConverter->convertWithRate($amount, $rate, $currency);
        }

        return $this->formatterFactory->create($this->locale, $currency)->currency($amount, $currency);
    }

    /**
     * Returns whether the currency and the rate is defined.
     */
    protected function isConfigured(): bool
    {
        return $this->quote && $this->rate;
    }
}

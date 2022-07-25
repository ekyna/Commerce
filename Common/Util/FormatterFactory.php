<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Util;

use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

/**
 * Class FormatterFactory
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormatterFactory
{
    /** @var array<int, Formatter> */
    protected array $cache = [];

    public function __construct(
        private readonly LocaleProviderInterface   $localeProvider,
        private readonly CurrencyProviderInterface $currencyProvider
    ) {
    }

    /**
     * Creates a new formatter.
     */
    public function create(string $locale = null, string $currency = null): Formatter
    {
        $locale = $locale ?? $this->localeProvider->getCurrentLocale();
        $currency = $currency ?? $this->currencyProvider->getCurrentCurrency();

        if (isset($this->cache[$key = strtolower("$locale-$currency")])) {
            return $this->cache[$key];
        }

        return $this->cache[$key] = new Formatter($locale, $currency);
    }

    /**
     * Returns the default formatter.
     */
    public function createDefault(): Formatter
    {
        return $this->create(
            $this->localeProvider->getFallbackLocale(),
            $this->currencyProvider->getFallbackCurrency()
        );
    }

    /**
     * Returns the formatter for the given context.
     */
    public function createFromContext(ContextInterface $context): Formatter
    {
        return $this->create($context->getLocale(), $context->getCurrency()->getCode());
    }
}

<?php

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
    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var CurrencyProviderInterface
     */
    protected $currencyProvider;

    /**
     * @var Formatter[]
     */
    protected $cache;


    /**
     * Constructor.
     *
     * @param LocaleProviderInterface   $localeProvider
     * @param CurrencyProviderInterface $currencyProvider
     */
    public function __construct(LocaleProviderInterface $localeProvider, CurrencyProviderInterface $currencyProvider)
    {
        $this->localeProvider = $localeProvider;
        $this->currencyProvider = $currencyProvider;
        $this->cache = [];
    }

    /**
     * Creates a new formatter.
     *
     * @param string|null $locale
     * @param string|null $currency
     *
     * @return Formatter
     */
    public function create(string $locale = null, string $currency = null)
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
     *
     * @return Formatter
     */
    public function createDefault()
    {
        return $this->create(
            $this->localeProvider->getFallbackLocale(),
            $this->currencyProvider->getFallbackCurrency()
        );
    }

    /**
     * Returns the formatter for the given context.
     *
     * @param ContextInterface $context
     *
     * @return Formatter
     */
    public function createFromContext(ContextInterface $context)
    {
        return $this->create($context->getLocale(), $context->getCurrency()->getCode());
    }
}

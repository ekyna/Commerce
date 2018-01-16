<?php

namespace Ekyna\Component\Commerce\Common\Util;

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
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param LocaleProviderInterface $localeProvider
     * @param string                  $defaultCurrency
     */
    public function __construct(LocaleProviderInterface $localeProvider, string $defaultCurrency)
    {
        $this->localeProvider = $localeProvider;
        $this->defaultCurrency = $defaultCurrency;
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
        $locale = $locale ? $locale : $this->localeProvider->getCurrentLocale();
        $currency = $currency ? $currency : $this->defaultCurrency;

        return new Formatter($locale, $currency);
    }
}

<?php

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

/**
 * Class CurrencyProvider
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyProvider implements CurrencyProviderInterface
{
    /**
     * @var CurrencyRepositoryInterface
     */
    protected $currencyRepository;

    /**
     * @var string
     */
    protected $fallbackCurrency;

    /**
     * @var string
     */
    protected $currentCurrency;


    /**
     * Constructor.
     *
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param string                      $fallbackCurrency
     * @param string                      $currentCurrency
     */
    public function __construct(
        CurrencyRepositoryInterface $currencyRepository,
        string $fallbackCurrency,
        string $currentCurrency = null
    ) {
        $this->currencyRepository = $currencyRepository;
        $this->fallbackCurrency = $fallbackCurrency;
        $this->currentCurrency = $currentCurrency;
    }

    /**
     * @inheritdoc
     */
    public function getAvailableCurrencies()
    {
        return $this->currencyRepository->findEnabledCodes();
    }

    /**
     * @inheritdoc
     */
    public function getFallbackCurrency()
    {
        return $this->fallbackCurrency;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentCurrency()
    {
        if ($this->currentCurrency) {
            return $this->currentCurrency;
        }

        return $this->currentCurrency = $this->getFallbackCurrency();
    }

    /**
     * @inheritDoc
     */
    public function setCurrentCurrency(string $currency)
    {
        if (!in_array($currency, $this->getAvailableCurrencies(), true)) {
            throw new UnexpectedValueException("Currency $currency is not available.");
        }

        $this->currentCurrency = $currency;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency(string $code = null)
    {
        return $this->currencyRepository->findOneByCode($code ?? $this->getCurrentCurrency());
    }
}

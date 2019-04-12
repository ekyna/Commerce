<?php

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
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
     * @inheritDoc
     */
    public function getAvailableCurrencies(): array
    {
        return $this->currencyRepository->findEnabledCodes();
    }

    /**
     * @inheritDoc
     */
    public function getFallbackCurrency(): string
    {
        return $this->fallbackCurrency;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCurrency(): string
    {
        if ($this->currentCurrency) {
            return $this->currentCurrency;
        }

        if (null !== $currency = $this->guessCurrency()) {
            $this->currentCurrency = $currency;
        }

        return $this->currentCurrency = $this->getFallbackCurrency();
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($currency): CurrencyProviderInterface
    {
        $currency = $currency instanceof CurrencyInterface ? $currency->getCode() : $currency;

        if (!is_string($currency)) {
            throw new UnexpectedValueException("Expected string or instance of " . CurrencyInterface::class);
        }

        if (!in_array($currency, $this->getAvailableCurrencies(), true)) {
            throw new UnexpectedValueException("Currency $currency is not available.");
        }

        $this->currentCurrency = $currency;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(string $code = null): CurrencyInterface
    {
        return $this->currencyRepository->findOneByCode($code ?? $this->getCurrentCurrency());
    }

    /**
     * Guesses the user currency.
     *
     * @return string|null
     */
    protected function guessCurrency(): ?string
    {
        return null;
    }
}

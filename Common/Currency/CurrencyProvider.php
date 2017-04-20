<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

/**
 * Class CurrencyProvider
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyProvider implements CurrencyProviderInterface
{
    protected CurrencyRepositoryInterface $currencyRepository;
    protected string                      $fallbackCurrency;
    protected ?string                     $currentCurrency = null;


    public function __construct(
        CurrencyRepositoryInterface $currencyRepository,
        string $fallbackCurrency
    ) {
        $this->currencyRepository = $currencyRepository;
        $this->fallbackCurrency = $fallbackCurrency;
    }

    public function getAvailableCurrencies(): array
    {
        return $this->currencyRepository->findEnabledCodes();
    }

    public function getFallbackCurrency(): string
    {
        return $this->fallbackCurrency;
    }

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
            throw new UnexpectedTypeException($currency, ['string', CurrencyInterface::class]);
        }

        if (!in_array($currency, $this->getAvailableCurrencies(), true)) {
            throw new UnexpectedValueException("Currency $currency is not available.");
        }

        $this->currentCurrency = $currency;

        return $this;
    }

    public function getCurrency(string $code = null): CurrencyInterface
    {
        return $this->currencyRepository->findOneByCode($code ?? $this->getCurrentCurrency());
    }

    public function getCurrencyRepository(): CurrencyRepositoryInterface
    {
        return $this->currencyRepository;
    }

    /**
     * Guesses the user currency.
     */
    protected function guessCurrency(): ?string
    {
        return null;
    }
}

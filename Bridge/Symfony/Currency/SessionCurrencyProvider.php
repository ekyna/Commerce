<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Currency;

use Ekyna\Component\Commerce\Common\Currency\CurrencyProvider;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SessionCurrencyProvider
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCurrencyProvider extends CurrencyProvider
{
    private const KEY = 'ekyna_commerce/currency';

    private RequestStack $requestStack;
    private string       $key;


    public function __construct(
        CurrencyRepositoryInterface $currencyRepository,
        RequestStack $requestStack,
        string $fallbackCurrency,
        string $key = self::KEY
    ) {
        parent::__construct($currencyRepository, $fallbackCurrency);

        $this->requestStack = $requestStack;
        $this->key = $key;
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($currency): CurrencyProviderInterface
    {
        parent::setCurrency($currency);

        $this->save();

        return $this;
    }

    public function getCurrentCurrency(): string
    {
        if ($this->currentCurrency) {
            return $this->currentCurrency;
        }

        try {
            $session = $this->requestStack->getSession();

            if ($session->has($this->key)) {
                return $this->currentCurrency = $session->get($this->key);
            }
        } catch (SessionNotFoundException $exception) {
        }

        $this->currentCurrency = parent::getCurrentCurrency();

        $this->save();

        return $this->currentCurrency;
    }

    private function save(): void
    {
        try {
            $this->requestStack->getSession()->set($this->key, $this->currentCurrency);
        } catch (SessionNotFoundException $exception) {
        }
    }
}

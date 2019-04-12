<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Currency;

use Ekyna\Component\Commerce\Common\Currency\CurrencyProvider;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionCurrencyProvider
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCurrencyProvider extends CurrencyProvider
{
    private const KEY = 'ekyna_commerce/currency';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $key;


    /**
     * Constructor.
     *
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param SessionInterface            $session
     * @param string                      $fallbackCurrency
     * @param string                      $key
     */
    public function __construct(
        CurrencyRepositoryInterface $currencyRepository,
        SessionInterface $session,
        string $fallbackCurrency,
        string $key = self::KEY
    ) {
        parent::__construct($currencyRepository, $fallbackCurrency);

        $this->session = $session;
        $this->key = $key;
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($currency): CurrencyProviderInterface
    {
        parent::setCurrency($currency);

        $this->session->set($this->key, $this->currentCurrency);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCurrency(): string
    {
        if ($this->currentCurrency) {
            return $this->currentCurrency;
        }

        if ($this->session->has($this->key)) {
            return $this->currentCurrency = $this->session->get($this->key);
        }

        return $this->currentCurrency = parent::getCurrentCurrency();
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use DateTimeInterface;
use Decimal\Decimal;

/**
 * Trait ExchangeSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ExchangeSubjectTrait
{
    use CurrencySubjectTrait;

    protected ?Decimal           $exchangeRate = null;
    protected ?DateTimeInterface $exchangeDate = null;


    /**
     * Returns the subject's base currency.
     */
    public function getBaseCurrency(): ?string
    {
        // Most of the exchange rate subjects store amounts in default currency.
        return null;
    }

    public function getExchangeRate(): ?Decimal
    {
        return $this->exchangeRate;
    }

    /**
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeRate(?Decimal $rate): ExchangeSubjectInterface
    {
        $this->exchangeRate = $rate;

        return $this;
    }

    public function getExchangeDate(): ?DateTimeInterface
    {
        return $this->exchangeDate;
    }

    /**
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeDate(?DateTimeInterface $date): ExchangeSubjectInterface
    {
        $this->exchangeDate = $date;

        return $this;
    }
}

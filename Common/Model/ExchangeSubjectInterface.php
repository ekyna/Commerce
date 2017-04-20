<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use DateTimeInterface;
use Decimal\Decimal;

/**
 * Interface ExchangeSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ExchangeSubjectInterface extends CurrencySubjectInterface
{
    /**
     * Returns the subject's base currency.
     */
    public function getBaseCurrency(): ?string;

    /**
     * Returns the exchange rate (DEFAULT/SUBJECT currencies pair).
     */
    public function getExchangeRate(): ?Decimal;

    /**
     * Sets the exchange rate (DEFAULT/SUBJECT currencies pair).
     */
    public function setExchangeRate(?Decimal $rate): ExchangeSubjectInterface;

    /**
     * Returns the exchange date.
     */
    public function getExchangeDate(): ?DateTimeInterface;

    /**
     * Sets the exchange date.
     */
    public function setExchangeDate(?DateTimeInterface $date): ExchangeSubjectInterface;
}

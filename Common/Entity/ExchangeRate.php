<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ExchangeRate
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExchangeRate implements ResourceInterface
{
    private ?int               $id    = null;
    private ?string            $base  = null;
    private ?string            $quote = null;
    private ?DateTimeInterface $date;
    private ?Decimal           $rate  = null;

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        if ($this->base && $this->quote && $this->date) {
            return sprintf('%s/%s %s', $this->base, $this->quote, $this->date->format('Y-m-d H:i'));
        }

        return 'New exchange rate';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBase(): ?string
    {
        return $this->base;
    }

    public function setBase(string $base): ExchangeRate
    {
        $this->base = $base;

        return $this;
    }

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(string $quote): ExchangeRate
    {
        $this->quote = $quote;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): ExchangeRate
    {
        $this->date = $date;

        return $this;
    }

    public function getRate(): ?Decimal
    {
        return $this->rate;
    }

    public function setRate(Decimal $rate): ExchangeRate
    {
        $this->rate = $rate;

        return $this;
    }
}

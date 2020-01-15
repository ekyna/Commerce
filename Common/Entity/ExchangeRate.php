<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ExchangeRate
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExchangeRate implements ResourceInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $base;

    /**
     * @var string
     */
    private $quote;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var float
     */
    private $rate;


    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf("%s/%s %s", $this->base, $this->quote, $this->date->format('Y-m-d H:i'));
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the base.
     *
     * @return string
     */
    public function getBase(): string
    {
        return $this->base;
    }

    /**
     * Sets the base.
     *
     * @param string $base
     *
     * @return ExchangeRate
     */
    public function setBase(string $base): self
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Returns the quote.
     *
     * @return string
     */
    public function getQuote(): string
    {
        return $this->quote;
    }

    /**
     * Sets the quote.
     *
     * @param string $quote
     *
     * @return ExchangeRate
     */
    public function setQuote(string $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Returns the date.
     *
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * Sets the date.
     *
     * @param \DateTime $date
     *
     * @return ExchangeRate
     */
    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Returns the rate.
     *
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * Sets the rate.
     *
     * @param float $rate
     *
     * @return ExchangeRate
     */
    public function setRate(float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }
}

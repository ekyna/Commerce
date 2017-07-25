<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

/**
 * Class Tax
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Tax
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $rate;

    /**
     * @var float
     */
    private $base = 0;

    /**
     * @var float
     */
    private $precision = 0;


    /**
     * Constructor.
     *
     * @param string $name
     * @param float  $rate
     * @param float  $base
     * @param int    $precision
     */
    public function __construct($name, $rate, $base = .0, $precision = 2)
    {
        $this->name = $name;
        $this->rate = (float)$rate;
        $this->precision = $precision;

        $this->addBase($base);
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the rate.
     *
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Returns the base.
     *
     * @return float
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Adds the base.
     *
     * @param float $base
     *
     * @return Tax
     */
    public function addBase($base)
    {
        $this->base += (float)$base;

        return $this;
    }

    /**
     * Multiply the tax (base).
     *
     * @param float $quantity
     *
     * @return Tax
     */
    public function multiply($quantity)
    {
        $this->base = round($this->base * $quantity, $this->precision);

        return $this;
    }

    /**
     * Returns the tax amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->round($this->base * $this->rate / 100);
    }

    /**
     * Rounds the given amount.
     *
     * @param float $amount
     *
     * @return float
     */
    private function round($amount)
    {
        return round($amount, $this->precision);
    }
}

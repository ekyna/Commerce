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
    private $amount = 0;

    /**
     * @var float
     */
    private $precision = 0;


    /**
     * Constructor.
     *
     * @param string $name
     * @param float  $rate
     * @param float  $amount
     * @param int    $precision
     */
    public function __construct($name, $rate, $amount = .0, $precision = 2)
    {
        $this->name = $name;
        $this->rate = (float)$rate;
        $this->precision = $precision;

        $this->addAmount($amount);
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
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Adds the amount.
     *
     * @param float $amount
     *
     * @return Tax
     */
    public function addAmount($amount)
    {
        $this->amount += (float)$amount;

        return $this;
    }

    /**
     * Multiply the amount.
     *
     * @param float $quantity
     *
     * @return Tax
     */
    public function multiply($quantity)
    {
        $this->amount = round($this->amount * $quantity, $this->precision);

        return $this;
    }
}

<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

/**
 * Class TaxAmount
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxAmount
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
     * Constructor.
     *
     * @param string $name
     * @param float  $rate
     * @param float  $amount
     */
    public function __construct($name, $rate, $amount = 0)
    {
        $this->name = $name;
        $this->rate = $rate;

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
     */
    public function addAmount($amount)
    {
        $this->amount += $amount;
    }
}

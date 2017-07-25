<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

/**
 * Class Result
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Result
{
    /**
     * @var float
     */
    private $base;

    /**
     * @var Tax[]
     */
    private $taxes;

    /**
     * @var float
     */
    private $total;

    /**
     * @var int
     */
    private $precision;


    /**
     * Constructor.
     *
     * @param float $base
     * @param int   $precision
     */
    public function __construct($base = .0, $precision = 2)
    {
        $this->clear();

        $this->addBase($base);

        $this->precision = 2;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $taxes = $this->taxes;
        $this->taxes = [];
        foreach ($taxes as $tax) {
            $this->taxes[] = clone $tax;
        }
    }

    /**
     * Clear the amounts.
     */
    public function clear()
    {
        $this->base = 0;
        $this->taxes = [];

        $this->clearTotal();
    }

    /**
     * Clear the amounts.
     */
    private function clearTotal()
    {
        $this->total = null;
    }

    /**
     * Merges the amounts.
     *
     * @param Result $result
     *
     * @return Result
     */
    public function merge(Result $result)
    {
        $this->addBase($result->getBase());

        foreach ($result->getTaxes() as $taxAmount) {
            $this->addTax($taxAmount->getName(), $taxAmount->getRate(), $taxAmount->getBase());
        }

        return $this;
    }

    /**
     * Adds the base.
     *
     * @param float $base
     *
     * @return Result
     */
    public function addBase($base)
    {
        $this->clearTotal();

        $this->base += $base;

        return $this;
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
     * Adds the tax.
     *
     * @param string $name
     * @param float  $rate
     * @param float  $base
     *
     * @return Result
     */
    public function addTax($name, $rate, $base)
    {
        $this->clearTotal();

        $taxAmount = null;
        foreach ($this->taxes as $tax) {
            if ($name === $tax->getName()) {
                $tax->addBase($base);

                return $this;
            }
        }

        $this->taxes[] = new Tax($name, $rate, $base, $this->precision);

        return $this;
    }

    /**
     * Multiply the result.
     *
     * @param float $quantity
     *
     * @return Result
     */
    public function multiply($quantity)
    {
        $this->clearTotal();

        $this->base = round($this->base * $quantity, $this->precision);

        foreach ($this->taxes as $tax) {
            $tax->multiply($quantity);
        }

        return $this;
    }

    /**
     * Returns the taxes.
     *
     * @return Tax[]
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * Returns the taxes rates.
     *
     * @return array
     */
    public function getTaxRates()
    {
        $rates = [];

        foreach ($this->taxes as $tax) {
            $rates[] = $tax->getRate();
        }

        return $rates;
    }

    /**
     * Returns the tax total.
     *
     * @return float
     */
    public function getTaxTotal()
    {
        $total = 0;

        foreach ($this->taxes as $tax) {
            $total += $tax->getAmount();
        }

        return $total;
    }

    /**
     * Returns the total.
     *
     * @return float
     */
    public function getTotal()
    {
        if (null === $this->total) {
            $this->total = $this->base + $this->getTaxTotal();
        }

        return $this->total;
    }
}

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
     * Constructor.
     *
     * @param float $base
     */
    public function __construct($base = .0)
    {
        $this->clear();

        $this->addBase($base);
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
            $this->addTax($taxAmount->getName(), $taxAmount->getRate(), $taxAmount->getAmount());
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
     * @param float $rate
     * @param float $amount
     *
     * @return Result
     */
    public function addTax($name, $rate, $amount)
    {
        $this->clearTotal();

        $taxAmount = null;
        foreach ($this->taxes as $tax) {
            if (0 === bccomp($tax->getRate(), $rate, 3)) {
                $tax->addAmount($amount);

                return $this;
            }
        }

        $this->taxes[] = new Tax($name, $rate, $amount);

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
     * Returns the total.
     *
     * @return float
     */
    public function getTotal()
    {
        if (null === $this->total) {
            $this->total = $this->base;

            foreach ($this->taxes as $tax) {
                $this->total += $tax->getAmount();
            }
        }

        return $this->total;
    }
}

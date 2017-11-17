<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

/**
 * Class Discount
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Adjustment
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $rate;


    /**
     * Constructor.
     *
     * @param string $name
     * @param float  $amount
     * @param float  $rate
     */
    public function __construct(string $name, float $amount, float $rate = null)
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->rate = $rate;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
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
     * Adds the amount.
     *
     * @param float $amount
     */
    public function addAmount(float $amount): void
    {
        $this->amount += $amount;
    }

    /**
     * Returns whether or not this adjustment is the same as the given one.
     *
     * @param Adjustment $adjustment
     *
     * @return bool
     */
    public function isSameAs(Adjustment $adjustment): bool
    {
        return $this->name === $adjustment->getName() && $this->rate === $adjustment->getRate();
    }
}

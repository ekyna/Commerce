<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;

/**
 * Class Discount
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Adjustment
{
    private string $name;
    private Decimal $amount;
    private ?Decimal $rate;

    public function __construct(string $name, Decimal $amount, Decimal $rate = null)
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->rate = $rate ?: new Decimal(0);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function getRate(): Decimal
    {
        return $this->rate;
    }

    public function addAmount(Decimal $amount): void
    {
        $this->amount = $this->amount->add($amount);
    }

    public function multiplyAmount(Decimal $factor): void
    {
        $this->amount = $this->amount->mul($factor);
    }

    /**
     * Returns whether this adjustment is the same as the given one.
     */
    public function isSameAs(Adjustment $adjustment): bool
    {
        return $this->name === $adjustment->getName()
            && $adjustment->getRate()->equals($this->rate);
    }
}

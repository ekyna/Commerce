<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Entity;

use Decimal\Decimal;

/**
 * Class StockStat
 * @package Ekyna\Component\Commerce\Stat\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockStat
{
    private ?int    $id   = null;
    private Decimal $inValue;
    private Decimal $soldValue;
    private ?string $date = null;


    public function __construct()
    {
        $this->inValue = new Decimal(0);
        $this->soldValue = new Decimal(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInValue(): Decimal
    {
        return $this->inValue;
    }

    public function setInValue(Decimal $inValue): self
    {
        $this->inValue = $inValue;

        return $this;
    }

    public function getSoldValue(): Decimal
    {
        return $this->soldValue;
    }

    public function setSoldValue(Decimal $soldValue): self
    {
        $this->soldValue = $soldValue;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }
}

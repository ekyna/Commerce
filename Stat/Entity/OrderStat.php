<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Entity;

use Decimal\Decimal;

/**
 * Class OrderStat
 * @package Ekyna\Component\Commerce\Stat\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStat extends AbstractStat
{
    protected Decimal $average;

    public function __construct()
    {
        parent::__construct();

        $this->average = new Decimal(0);
    }

    public function getAverage(): Decimal
    {
        return $this->average;
    }

    public function setAverage(Decimal $average): self
    {
        $this->average = $average;

        return $this;
    }

    protected function getMap(): array
    {
        return [
            'revenue'  => 'decimal',
            'shipping' => 'decimal',
            'cost'     => 'decimal',
            'count'    => 'int',
            'average'  => 'decimal',
        ];
    }
}

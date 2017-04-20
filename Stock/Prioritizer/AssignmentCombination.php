<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;

/**
 * Class AssignmentCombination
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AssignmentCombination
{
    /**
     * [assignment id => releasable quantity]
     *
     * @var array<int, Decimal>
     */
    public array $map;

    /**
     * The difference between sum and aimed quantity.
     */
    public Decimal $diff;

    /**
     * The sum of releasable quantity.
     */
    public Decimal $sum;

    /**
     * The size of the map.
     */
    public int $size;


    /**
     * @param array<int, Decimal> $map
     */
    public function __construct(array $map, Decimal $diff)
    {
        $this->map = $map;
        $this->diff = $diff;
        $this->sum = Decimal::sum($map);
        $this->size = count($map);
    }
}

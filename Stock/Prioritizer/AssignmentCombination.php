<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;

/**
 * Class AssignmentCombination
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AssignmentCombination
{
    /**
     * The sum of releasable quantity.
     */
    public readonly Decimal $sum;

    /**
     * The size of the map.
     */
    public readonly int $size;

    /**
     * @param array<int, Decimal> $map  [assignment id => releasable quantity]
     * @param Decimal             $diff The difference between sum and aimed quantity.
     */
    public function __construct(
        public readonly array $map,
        public readonly Decimal $diff
    ) {
        $this->sum = Decimal::sum($map);
        $this->size = count($map);
    }
}

<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

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
     * @var []
     */
    public $map;

    /**
     * The difference between sum and aimed quantity.
     *
     * @var float
     */
    public $diff;

    /**
     * The sum of releasable quantity.
     *
     * @var float
     */
    public $sum;

    /**
     * The size of the map.
     *
     * @var int
     */
    public $size;


    /**
     * Constructor.
     *
     * @param int[] $map
     * @param float $diff
     */
    public function __construct(array $map, $diff)
    {
        $this->map = $map;
        $this->diff = $diff;
        $this->sum = array_sum($map);
        $this->size = count($map);
    }
}

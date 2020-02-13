<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class StateFilter
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StateFilter implements FilterInterface
{
    /**
     * @var string[]
     */
    private $states;


    /**
     * Constructor.
     *
     * @param array $states
     */
    public function __construct(array $states)
    {
        $this->states = $states;
    }

    /**
     * @inheritDoc
     */
    public function filter(StockUnitInterface $unit): bool
    {
        return in_array($unit->getState(), $this->states, true);
    }
}

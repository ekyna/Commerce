<?php

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Class StockStates
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockStates
{
    const STATE_IN_STOCK     = 'in_stock';
    const STATE_OUT_OF_STOCK = 'out_of_stock';
    const STATE_PRE_ORDER    = 'pre_order';


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getStates()
    {
        return [
            static::STATE_IN_STOCK,
            static::STATE_OUT_OF_STOCK,
            static::STATE_PRE_ORDER,
        ];
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isValidState($state)
    {
        return in_array($state, static::getStates(), true);
    }
}

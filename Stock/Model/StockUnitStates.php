<?php

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Class StockUnitStates
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockUnitStates
{
    const STATE_NEW     = 'new';
    const STATE_PENDING = 'pending';
    const STATE_OPENED  = 'opened';
    const STATE_CLOSED  = 'closed';


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getStates()
    {
        return [
            static::STATE_NEW,
            static::STATE_PENDING,
            static::STATE_OPENED,
            static::STATE_CLOSED,
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

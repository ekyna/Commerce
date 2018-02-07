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
    const STATE_READY   = 'ready';
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
            static::STATE_READY,
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

    /**
     * Returns whether the state A has a better availability than the state B.
     *
     * @param string $stateA
     * @param string $stateB
     *
     * @return bool
     */
    static public function isBetterAvailability($stateA, $stateB)
    {
        if ($stateA === $stateB) {
            return false;
        }

        switch ($stateA) {
            // 'pending' is better than 'new' or 'closed'
            case static::STATE_PENDING:
                return in_array($stateB, [static::STATE_NEW, static::STATE_CLOSED], true);
            // 'ready' is better than 'new', 'pending' or 'closed'
            case static::STATE_READY:
                return in_array($stateB, [static::STATE_NEW, static::STATE_PENDING, static::STATE_CLOSED], true);
        }

        return false;
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}

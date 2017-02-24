<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

/**
 * Class SupplierOrderStates
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderStates
{
    const STATE_NEW         = 'new';
    const STATE_ORDERED     = 'ordered';
    const STATE_PARTIAL     = 'partial';
    const STATE_COMPLETED   = 'completed';
    const STATE_CANCELLED   = 'cancelled';


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getStates()
    {
        return [
            static::STATE_NEW,
            static::STATE_ORDERED,
            static::STATE_PARTIAL,
            static::STATE_COMPLETED,
            static::STATE_CANCELLED,
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
     * Returns the deletable states.
     *
     * @return array
     */
    static public function getDeletableStates()
    {
        return [
            static::STATE_NEW,
            static::STATE_CANCELLED,
        ];
    }

    /**
     * Returns whether the given state is a deletable state.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isDeletableState($state)
    {
        return in_array($state, static::getDeletableStates(), true);
    }

    /**
     * Returns the states which must result in a stock management.
     *
     * @return array
     */
    static public function getStockStates()
    {
        return [
            static::STATE_ORDERED,
            static::STATE_PARTIAL,
            static::STATE_COMPLETED
        ];
    }

    /**
     * Returns whether the given state is a stock state.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isStockState($state)
    {
        return in_array($state, static::getStockStates(), true);
    }
}

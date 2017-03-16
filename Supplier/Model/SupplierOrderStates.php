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
     * Returns whether the state has changed from a stockable state to a deletable state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedToDeletable(array $cs)
    {
        return (isset($cs[0]) && isset($cs[1]))
            && static::isStockableState($cs[0])
            && static::isDeletableState($cs[1]);
    }

    /**
     * Returns the states which must result in a stock management.
     *
     * @return array
     */
    static public function getStockableStates()
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
    static public function isStockableState($state)
    {
        return in_array($state, static::getStockableStates(), true);
    }

    // TODO Change the following methods (see order states)

    /**
     * Returns whether the state has changed from a deletable state to a stockable state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedToStock(array $cs)
    {
        return (isset($cs[0]) && isset($cs[1]))
            && static::isDeletableState($cs[0])
            && static::isStockableState($cs[1]);
    }
}

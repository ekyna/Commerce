<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Class OrderStates
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderStates
{
    const STATE_NEW       = 'new';
    const STATE_PENDING   = 'pending';
    const STATE_REFUSED   = 'refused';
    const STATE_ACCEPTED  = 'accepted';
    const STATE_COMPLETED = 'completed';
    const STATE_REFUNDED  = 'refunded';
    const STATE_CANCELLED = 'cancelled';


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
            static::STATE_REFUSED,
            static::STATE_ACCEPTED,
            static::STATE_COMPLETED,
            static::STATE_REFUNDED,
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
            static::STATE_REFUSED,
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
     * Returns whether or not the state has changed
     * from a non deletable state to a deletable state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedToDeletable(array $cs)
    {
        return (isset($cs[0]) && isset($cs[1]))
            && !static::isDeletableState($cs[0])
            && static::isDeletableState($cs[1]);
    }

    /**
     * Returns whether or not the state has changed
     * from a deletable state to a non deletable state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedFromDeletable(array $cs)
    {
        return (isset($cs[0]) && isset($cs[1]))
            && static::isDeletableState($cs[0])
            && !static::isDeletableState($cs[1]);
    }

    /**
     * Returns the states which must result in a stock management.
     *
     * @return array
     */
    static public function getStockableStates()
    {
        return [
            static::STATE_ACCEPTED,
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

    /**
     * Returns whether or not the state has changed
     * from a non stockable state to a stockable state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedToStockable(array $cs)
    {
        return (isset($cs[0]) && isset($cs[1]))
            && !static::isStockableState($cs[0])
            && static::isStockableState($cs[1]);
    }

    /**
     * Returns whether or not the state has changed
     * from a stockable state to a non stockable state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedFromStockable(array $cs)
    {
        return (isset($cs[0]) && isset($cs[1]))
            && static::isStockableState($cs[0])
            && !static::isStockableState($cs[1]);
    }
}

<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class ShipmentStates
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ShipmentStates
{
    const STATE_NONE        = 'none';
    const STATE_NEW         = 'new';
//    const STATE_CHECKOUT    = 'checkout';
//    const STATE_ONHOLD      = 'onhold';
    const STATE_PENDING     = 'pending';
//    const STATE_BACKORDERED = 'backordered';
    const STATE_READY       = 'ready';
    const STATE_SHIPPED     = 'shipped';
    const STATE_PARTIAL     = 'partial';
    const STATE_COMPLETED   = 'completed';
    const STATE_RETURNED    = 'returned';
    const STATE_CANCELLED   = 'cancelled';


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getStates()
    {
        return [
            static::STATE_NONE,
            static::STATE_NEW,
//            static::STATE_CHECKOUT,
//            static::STATE_ONHOLD,
            static::STATE_PENDING,
//            static::STATE_BACKORDERED,
            static::STATE_READY,
            static::STATE_SHIPPED,
            static::STATE_PARTIAL,
            static::STATE_COMPLETED,
            static::STATE_RETURNED,
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
     * Returns the notifiable states.
     *
     * @return array
     */
    static public function getNotifiableStates()
    {
        return [
            static::STATE_PENDING,
            static::STATE_READY,
            static::STATE_SHIPPED,
        ];
    }

    /**
     * Returns whether the given state is a notifiable state.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isNotifiableState($state)
    {
        return in_array($state, static::getNotifiableStates(), true);
    }

    /**
     * Returns the deletable states.
     *
     * @return array
     */
    static public function getDeletableStates()
    {
        return [
            static::STATE_NONE,
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
     * Returns the debit stock states.
     *
     * @return array
     */
    static public function getStockStates()
    {
        return [
            static::STATE_SHIPPED,
            static::STATE_COMPLETED,
            static::STATE_RETURNED,
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
            && static::isStockState($cs[0])
            && static::isDeletableState($cs[1]);
    }

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
            && static::isStockState($cs[1]);
    }
}

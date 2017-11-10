<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

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
    const STATE_READY     = 'ready';
    const STATE_SHIPPED   = 'shipped';
    const STATE_PARTIAL   = 'partial';
    const STATE_COMPLETED = 'completed';
    const STATE_RETURNED  = 'returned';
    const STATE_CANCELED  = 'canceled';


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
            static::STATE_CANCELED,
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
     * Returns the shipped states.
     *
     * @return array
     */
    static public function getShippedStates()
    {
        return [
            static::STATE_NONE, // TODO why ?
            static::STATE_SHIPPED,
            static::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether the given state is a shipped state.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isShippedState($state)
    {
        return in_array($state, static::getShippedStates(), true);
    }

    /**
     * Returns the returned states.
     *
     * @return array
     */
    static public function getReturnedStates()
    {
        return [
            static::STATE_NONE, // TODO why ?
            static::STATE_RETURNED
        ];
    }

    /**
     * Returns whether the given state is a returned state.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isReturnedState($state)
    {
        return in_array($state, static::getReturnedStates(), true);
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
            static::STATE_CANCELED,
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
        return is_null($state) || in_array($state, static::getDeletableStates(), true);
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
        return static::assertValidChangeSet($cs)
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
        return static::assertValidChangeSet($cs)
            && static::isDeletableState($cs[0])
            && !static::isDeletableState($cs[1]);
    }

    /**
     * Returns the debit stock states.
     *
     * @return array
     */
    static public function getStockableStates()
    {
        return [
            static::STATE_READY,
            static::STATE_PARTIAL,
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
        return static::assertValidChangeSet($cs)
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
        return static::assertValidChangeSet($cs)
            && static::isStockableState($cs[0])
            && !static::isStockableState($cs[1]);
    }

    /**
     * Returns whether or not the change set is valid.
     *
     * @param array $cs
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    static private function assertValidChangeSet(array $cs)
    {
        if (
            array_key_exists(0, $cs) &&
            array_key_exists(1, $cs) &&
            (is_null($cs[0]) || static::isValidState($cs[0])) &&
            (is_null($cs[1]) || static::isValidState($cs[1]))
        ) {
            return true;
        }

        throw new InvalidArgumentException("Unexpected order state change set.");
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

<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

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
    const STATE_CANCELED  = 'canceled';


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
     * Returns the deletable states.
     *
     * @return array
     */
    static public function getDeletableStates()
    {
        return [
            static::STATE_NEW,
            static::STATE_CANCELED,
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
        return is_null(null === $state) || in_array($state, static::getDeletableStates(), true);
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
     * Returns the states which must result in a stock management.
     *
     * @return array
     */
    static public function getStockableStates()
    {
        return [
            static::STATE_ACCEPTED,
            static::STATE_REFUNDED,
            static::STATE_COMPLETED,
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
        return !is_null($state) && in_array($state, static::getStockableStates(), true);
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

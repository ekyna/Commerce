<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class PaymentStates
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentStates
{
    const STATE_NEW         = 'new';
    const STATE_PENDING     = 'pending';
    const STATE_CAPTURED    = 'captured';
    const STATE_FAILED      = 'failed';
    const STATE_CANCELLED   = 'cancelled';
    const STATE_REFUNDED    = 'refunded';
    const STATE_AUTHORIZED  = 'authorized';
    const STATE_OUTSTANDING = 'outstanding';
    const STATE_SUSPENDED   = 'suspended';
    const STATE_EXPIRED     = 'expired';
    const STATE_UNKNOWN     = 'unknown';


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
            static::STATE_CAPTURED,
            static::STATE_FAILED,
            static::STATE_CANCELLED,
            static::STATE_REFUNDED,
            static::STATE_AUTHORIZED,
            static::STATE_OUTSTANDING,
            static::STATE_SUSPENDED,
            static::STATE_EXPIRED,
            static::STATE_UNKNOWN,
        ];
    }

    /**
     * Returns whether or not the given state is valid.
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
            static::STATE_CAPTURED,
            static::STATE_FAILED,
            static::STATE_REFUNDED,
            static::STATE_OUTSTANDING,
        ];
    }

    /**
     * Returns whether or not the given state is a notifiable state.
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
            static::STATE_NEW,
            static::STATE_CANCELLED,
        ];
    }

    /**
     * Returns whether or not the given state is a deletable state.
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
     * Returns the paid states.
     *
     * @return array
     */
    static public function getPaidStates()
    {
        return [
            static::STATE_CAPTURED,
            static::STATE_AUTHORIZED,
            static::STATE_OUTSTANDING,
        ];
    }

    /**
     * Returns whether or not the given state is a paid state.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isPaidState($state)
    {
        return in_array($state, static::getPaidStates(), true);
    }

    /**
     * Returns whether or not the state has changed
     * from a non paid state to a paid state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedToPaid(array $cs)
    {
        return static::assertValidChangeSet($cs)
            && !static::isPaidState($cs[0])
            && static::isPaidState($cs[1]);
    }

    /**
     * Returns whether or not the state has changed
     * from a paid state to a non paid state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedFromPaid(array $cs)
    {
        return static::assertValidChangeSet($cs)
            && static::isPaidState($cs[0])
            && !static::isPaidState($cs[1]);
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
}

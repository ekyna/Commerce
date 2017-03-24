<?php

namespace Ekyna\Component\Commerce\Payment\Model;

/**
 * Class PaymentStates
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentStates
{
    const STATE_NEW        = 'new';
    const STATE_PENDING    = 'pending';
    const STATE_CAPTURED   = 'captured';
    const STATE_FAILED     = 'failed';
    const STATE_CANCELLED  = 'cancelled';
    const STATE_REFUNDED   = 'refunded';
    const STATE_AUTHORIZED = 'authorized';
    const STATE_SUSPENDED  = 'suspended';
    const STATE_EXPIRED    = 'expired';
    const STATE_UNKNOWN    = 'unknown';


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
        return null === $state || in_array($state, static::getDeletableStates(), true);
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
        return null !== $state && in_array($state, static::getPaidStates(), true);
    }
}

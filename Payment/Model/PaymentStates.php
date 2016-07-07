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
            static::STATE_AUTHORIZED,
            static::STATE_SUSPENDED,
            static::STATE_EXPIRED,
            static::STATE_UNKNOWN,
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

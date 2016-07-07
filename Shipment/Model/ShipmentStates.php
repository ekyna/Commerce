<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class ShipmentStates
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ShipmentStates
{
    const STATE_CHECKOUT    = 'checkout';
//    const STATE_ONHOLD      = 'onhold';
    const STATE_PENDING     = 'pending';
//    const STATE_BACKORDERED = 'backordered';
    const STATE_READY       = 'ready';
    const STATE_SHIPPED     = 'shipped';
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
            static::STATE_CHECKOUT,
//            static::STATE_ONHOLD,
            static::STATE_PENDING,
//            static::STATE_BACKORDERED,
            static::STATE_READY,
            static::STATE_SHIPPED,
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
}

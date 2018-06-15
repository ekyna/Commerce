<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class NotificationTypes
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class NotificationTypes
{
    const MANUAL           = 'manual';
    const CART_REMIND      = 'cart_remind';
    const ORDER_ACCEPTED   = 'order_accepted';
    const QUOTE_REMIND     = 'quote_remind';
    const PAYMENT_CAPTURED = 'payment_captured';
    const PAYMENT_EXPIRED  = 'payment_expired';
    const SHIPMENT_SHIPPED = 'shipment_shipped';
    const SHIPMENT_PARTIAL = 'shipment_partial';
    const RETURN_PENDING   = 'return_pending';
    const RETURN_RECEIVED  = 'return_received';


    /**
     * Returns all the types.
     *
     * @return array
     */
    static public function getTypes()
    {
        return [
            static::MANUAL,
            static::CART_REMIND,
            static::ORDER_ACCEPTED,
            static::QUOTE_REMIND,
            static::PAYMENT_CAPTURED,
            static::PAYMENT_EXPIRED,
            static::SHIPMENT_SHIPPED,
            static::SHIPMENT_PARTIAL,
            static::RETURN_PENDING,
            static::RETURN_RECEIVED,
        ];
    }

    /**
     * Returns whether the given type is valid or not.
     *
     * @param string $type
     * @param bool   $throw
     *
     * @return bool
     */
    static public function isValidType($type, $throw = true)
    {
        if (in_array($type, static::getTypes(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid notification type.');
        }

        return false;
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class GatewayActions
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class GatewayActions
{
    const SHIP              = 'ship';
    const CANCEL            = 'cancel';
    const COMPLETE          = 'complete';
    const TRACK             = 'track';
    const PROVE             = 'prove';
    const PRINT_LABEL       = 'print_label';
    const LIST_RELAY_POINTS = 'list_relay_points';
    const GET_RELAY_POINT   = 'get_relay_point';


    /**
     * Returns the api actions.
     *
     * @return array
     */
    public static function getApiActions()
    {
        return [
            static::LIST_RELAY_POINTS,
            static::GET_RELAY_POINT,
        ];
    }

    /**
     * Returns the shipment actions.
     *
     * @return array
     */
    public static function getShipmentActions()
    {
        return [
            static::SHIP,
            static::CANCEL,
            static::COMPLETE,
            static::PRINT_LABEL,
        ];
    }

    /**
     * Returns all the actions.
     *
     * @return array
     */
    public static function getActions()
    {
        return [
            static::SHIP,
            static::CANCEL,
            static::COMPLETE,
            static::TRACK,
            static::PROVE,
            static::PRINT_LABEL,
            static::LIST_RELAY_POINTS,
            static::GET_RELAY_POINT,
        ];
    }

    /**
     * Returns whether the given action is valid.
     *
     * @param string $action
     * @param bool   $throw
     *
     * @return bool
     */
    public static function isValid($action, $throw = false)
    {
        if (in_array($action, static::getActions(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException("Unknown gateway action '$action'.");
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

<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class GatewayActions
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class GatewayActions
{
    public const SHIP              = 'ship';
    public const CANCEL            = 'cancel';
    public const COMPLETE          = 'complete';
    public const TRACK             = 'track';
    public const PROVE             = 'prove';
    public const PRINT_LABEL       = 'print_label';
    public const LIST_RELAY_POINTS = 'list_relay_points';
    public const GET_RELAY_POINT   = 'get_relay_point';


    /**
     * Returns the api actions.
     */
    public static function getApiActions(): array
    {
        return [
            self::LIST_RELAY_POINTS,
            self::GET_RELAY_POINT,
        ];
    }

    /**
     * Returns the shipment actions.
     */
    public static function getShipmentActions(): array
    {
        return [
            self::SHIP,
            self::CANCEL,
            self::COMPLETE,
            self::PRINT_LABEL,
        ];
    }

    /**
     * Returns all the actions.
     */
    public static function getActions(): array
    {
        return [
            self::SHIP,
            self::CANCEL,
            self::COMPLETE,
            self::TRACK,
            self::PROVE,
            self::PRINT_LABEL,
            self::LIST_RELAY_POINTS,
            self::GET_RELAY_POINT,
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
    public static function isValid(string $action, bool $throw = false): bool
    {
        if (in_array($action, self::getActions(), true)) {
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

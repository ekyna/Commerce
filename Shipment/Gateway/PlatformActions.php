<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class PlatformActions
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PlatformActions
{
    const EXPORT       = 'export';
    const IMPORT       = 'import';
    const SHIP         = 'ship';
    const CANCEL       = 'cancel';
    const PRINT_LABELS = 'print_labels';


    /**
     * Returns the global actions.
     *
     * @return array
     */
    public static function getGlobalActions()
    {
        return [
            static::EXPORT,
            static::IMPORT,
        ];
    }

    /**
     * Returns the mass/batch actions.
     *
     * @return array
     */
    public static function getMassActions()
    {
        return [
            static::SHIP,
            static::CANCEL,
            static::PRINT_LABELS,
        ];
    }

    /**
     * Returns all the capabilities.
     *
     * @return array
     */
    public static function getActions()
    {
        return [
            static::EXPORT,
            static::IMPORT,
            static::SHIP,
            static::CANCEL,
            static::PRINT_LABELS,
        ];
    }

    /**
     * Returns whether the given capability is valid.
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
            throw new InvalidArgumentException("Unknown platform action '$action'.");
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

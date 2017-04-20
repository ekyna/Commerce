<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class PlatformActions
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PlatformActions
{
    public const EXPORT       = 'export';
    public const IMPORT       = 'import';
    public const SHIP         = 'ship';
    public const CANCEL       = 'cancel';
    public const PRINT_LABELS = 'print_labels';


    /**
     * Returns the global actions.
     */
    public static function getGlobalActions(): array
    {
        return [
            self::EXPORT,
            self::IMPORT,
        ];
    }

    /**
     * Returns the mass/batch actions.
     */
    public static function getMassActions(): array
    {
        return [
            self::SHIP,
            self::CANCEL,
            self::PRINT_LABELS,
        ];
    }

    /**
     * Returns all the capabilities.
     */
    public static function getActions(): array
    {
        return [
            self::EXPORT,
            self::IMPORT,
            self::SHIP,
            self::CANCEL,
            self::PRINT_LABELS,
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
    public static function isValid(string $action, bool $throw = false): bool
    {
        if (in_array($action, self::getActions(), true)) {
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

<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class Unit
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Units
{
    const PIECE = 'piece';

    // Weight
    const KILOGRAM = 'kilogram';
    const GRAM     = 'gram';

    // Volume
    const CUBIC_METER = 'cubic_meter';
    const LITER       = 'liter';
    const MILLILITER  = 'milliliter';

    // Duration
    const DAY    = 'day';
    const HOUR   = 'hour';
    const MINUTE = 'minute';
    const SECOND = 'second';


    /**
     * Returns all the units.
     *
     * @return string[]
     */
    static function getUnits()
    {
        return [
            static::PIECE,
            static::KILOGRAM,
            static::GRAM,
            static::CUBIC_METER,
            static::LITER,
            static::MILLILITER,
            static::DAY,
            static::HOUR,
            static::MINUTE,
            static::SECOND,
        ];
    }

    /**
     * Returns whether the given unit is valid.
     *
     * @param string $unit
     * @param bool   $throw
     *
     * @return bool
     */
    static function isValid($unit, $throw = false)
    {
        if (in_array($unit, static::getUnits(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException("Invalid unit '$unit'.");
        }

        return false;
    }

    /**
     * Returns the symbol for the given unit.
     *
     * @param string $unit
     *
     * @return int
     */
    static function getSymbol($unit)
    {
        switch ($unit) {
            case static::PIECE:
                return 'pcs';
            case static::KILOGRAM:
                return 'kg';
            case static::GRAM:
                return 'g';
            case static::CUBIC_METER:
                return 'm3';
            case static::LITER:
                return 'l';
            case static::MILLILITER:
                return 'ml';
            case static::DAY:
                return 'pcs';
            case static::HOUR:
                return 'pcs';
            case static::MINUTE:
                return 'pcs';
            case static::SECOND:
                return 'pcs';
            default:
                throw new InvalidArgumentException("Invalid unit '$unit'.");
        }
    }

    /**
     * Returns the rounding precision for the given unit.
     *
     * @param string $unit
     *
     * @return int
     */
    static function getPrecision($unit)
    {
        switch ($unit) {
            case static::PIECE:
            case static::GRAM:
            case static::MILLILITER:
            case static::DAY:
            case static::MINUTE:
            case static::SECOND:
                return 0;
            case static::HOUR:
                return 2;
            case static::KILOGRAM:
            case static::CUBIC_METER:
            case static::LITER:
                return 3;
            default:
                throw new InvalidArgumentException("Invalid unit '$unit'.");
        }
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
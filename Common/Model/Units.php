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
    const PIECE       = 'piece';

    // Length
    const METER       = 'meter';
    const CENTIMETER  = 'centimeter';
    const MILLIMETER  = 'millimeter';
    const INCH        = 'inch';
    const FOOT        = 'foot';

    // Weight
    const KILOGRAM    = 'kilogram';
    const GRAM        = 'gram';

    // Volume
    const CUBIC_METER = 'cubic_meter';
    const LITER       = 'liter';
    const MILLILITER  = 'milliliter';

    // Duration
    const DAY         = 'day';
    const HOUR        = 'hour';
    const MINUTE      = 'minute';
    const SECOND      = 'second';


    /**
     * Returns all the units.
     *
     * @return string[]
     */
    static function getUnits()
    {
        return [
            static::PIECE,
            // Length
            static::METER,
            static::CENTIMETER,
            static::MILLIMETER,
            static::INCH,
            static::FOOT,
            // Weight
            static::KILOGRAM,
            static::GRAM,
            // Volume
            static::CUBIC_METER,
            static::LITER,
            static::MILLILITER,
            // Duration
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

            // Length
            case static::METER:
                return 'm';
            case static::CENTIMETER:
                return 'cm';
            case static::MILLIMETER:
                return 'mm';
            case static::INCH:
                return 'in';
            case static::FOOT:
                return 'ft';

            // Weight
            case static::KILOGRAM:
                return 'kg';
            case static::GRAM:
                return 'g';

            // Volume
            case static::CUBIC_METER:
                return 'm³';
            case static::LITER:
                return 'L';
            case static::MILLILITER:
                return 'mL';

            // Duration
            case static::DAY:
                return 'days';
            case static::HOUR:
                return 'hours';
            case static::MINUTE:
                return 'minutes';
            case static::SECOND:
                return 's';

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
            case static::MILLIMETER:
            case static::GRAM:
            case static::MILLILITER:
            case static::DAY:
            case static::MINUTE:
            case static::SECOND:
                return 0;
            case static::CENTIMETER:
                return 1;
            case static::INCH:
            case static::FOOT:
            case static::HOUR:
                return 2;
            case static::METER:
            case static::KILOGRAM:
            case static::CUBIC_METER:
            case static::LITER:
                return 3;
            default:
                throw new InvalidArgumentException("Invalid unit '$unit'.");
        }
    }

    /**
     * Rounds the given value for the given unit.
     *
     * @param float  $value
     * @param string $unit
     *
     * @return float|int
     */
    static function round($value, $unit = 'piece')
    {
        if (0 < $precision = static::getPrecision($unit)) {
            $divider = pow(10, $precision);

            return round(floor($value * $divider) / $divider, $precision);
        }

        return floor($value);
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
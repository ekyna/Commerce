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

    // Length
    const METRE      = 'metre';
    const CENTIMETRE = 'centimetre';
    const MILLIMETRE = 'millimetre';
    const INCH       = 'inch';
    const FOOT       = 'foot';

    // Weight
    const KILOGRAM = 'kilogram';
    const GRAM     = 'gram';

    // Volume
    const CUBIC_METRE = 'cubic_metre';
    const LITRE       = 'litre';
    const MILLILITRE  = 'millilitre';

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
            // Length
            static::METRE,
            static::CENTIMETRE,
            static::MILLIMETRE,
            static::INCH,
            static::FOOT,
            // Weight
            static::KILOGRAM,
            static::GRAM,
            // Volume
            static::CUBIC_METRE,
            static::LITRE,
            static::MILLILITRE,
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
            case static::METRE:
                return 'm';
            case static::CENTIMETRE:
                return 'cm';
            case static::MILLIMETRE:
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
            case static::CUBIC_METRE:
                return 'm³';
            case static::LITRE:
                return 'L';
            case static::MILLILITRE:
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
            case static::MILLIMETRE:
            case static::GRAM:
            case static::MILLILITRE:
            case static::DAY:
            case static::MINUTE:
            case static::SECOND:
                return 0;
            case static::CENTIMETRE:
                return 1;
            case static::INCH:
            case static::FOOT:
            case static::HOUR:
                return 2;
            case static::METRE:
            case static::KILOGRAM:
            case static::CUBIC_METRE:
            case static::LITRE:
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
<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

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
    static function getUnits(): array
    {
        return [
            self::PIECE,
            // Length
            self::METER,
            self::CENTIMETER,
            self::MILLIMETER,
            self::INCH,
            self::FOOT,
            // Weight
            self::KILOGRAM,
            self::GRAM,
            // Volume
            self::CUBIC_METER,
            self::LITER,
            self::MILLILITER,
            // Duration
            self::DAY,
            self::HOUR,
            self::MINUTE,
            self::SECOND,
        ];
    }

    /**
     * Returns whether the given unit is valid.
     *
     * @param string $unit
     * @param bool   $throwException
     *
     * @return bool
     */
    static function isValid(string $unit, bool $throwException = false): bool
    {
        if (in_array($unit, self::getUnits(), true)) {
            return true;
        }

        if ($throwException) {
            throw new UnexpectedValueException("Unknown unit '$unit'.");
        }

        return false;
    }

    /**
     * Returns the symbol for the given unit.
     *
     * @param string $unit
     *
     * @return string
     */
    static function getSymbol(string $unit): string
    {
        switch ($unit) {
            case self::PIECE:
                return 'pcs';

            // Length
            case self::METER:
                return 'm';
            case self::CENTIMETER:
                return 'cm';
            case self::MILLIMETER:
                return 'mm';
            case self::INCH:
                return 'in';
            case self::FOOT:
                return 'ft';

            // Weight
            case self::KILOGRAM:
                return 'kg';
            case self::GRAM:
                return 'g';

            // Volume
            case self::CUBIC_METER:
                return 'm³';
            case self::LITER:
                return 'L';
            case self::MILLILITER:
                return 'mL';

            // Duration
            case self::DAY:
                return 'days';
            case self::HOUR:
                return 'hours';
            case self::MINUTE:
                return 'minutes';
            case self::SECOND:
                return 's';

            default:
                throw new UnexpectedValueException("Unknown unit '$unit'.");
        }
    }

    /**
     * Returns the rounding precision for the given unit.
     *
     * @param string $unit
     *
     * @return int
     */
    static function getPrecision(string $unit): int
    {
        switch ($unit) {
            case self::PIECE:
            case self::MILLIMETER:
            case self::GRAM:
            case self::MILLILITER:
            case self::DAY:
            case self::MINUTE:
            case self::SECOND:
                return 0;
            case self::CENTIMETER:
                return 1;
            case self::INCH:
            case self::FOOT:
            case self::HOUR:
                return 2;
            case self::METER:
            case self::KILOGRAM:
            case self::CUBIC_METER:
            case self::LITER:
                return 3;
            default:
                throw new UnexpectedValueException("Unknown unit '$unit'.");
        }
    }

    /**
     * Rounds the given value for the given unit.
     *
     * @param float  $value
     * @param string $unit
     *
     * @return float
     */
    static function round(float $value, string $unit = self::PIECE): float
    {
        if (0 < $precision = self::getPrecision($unit)) {
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